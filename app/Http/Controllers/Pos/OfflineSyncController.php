<?php

namespace App\Http\Controllers\Pos;

use App\Casts\OrderTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\OrderService;
use App\Support\Business;
use App\Support\Phone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * Oflayn kassa uchun API (sessiya orqali, oddiy foydalanuvchi).
 *
 * snapshot — mahsulotlar, kategoriyalar, mijozlar: brauzer o'zida saqlaydi.
 * sync — brauzerda to'plangan sotuvlar; har biri UUID bilan, takror
 * yuborilsa ikkinchi marta yozilmaydi.
 */
class OfflineSyncController extends Controller
{
    public function snapshot(Request $request): JsonResponse
    {
        $companyId = (int) $request->user()->companyId();
        $biz = Business::current();

        return response()->json([
            'fetched_at' => now()->toIso8601String(),
            'company' => [
                'id' => $companyId,
                'name' => $request->user()->company?->name ?? $request->user()->ownedCompany?->name,
                'business_type' => $biz->value,
            ],
            'terms' => [
                'takeaway' => $biz->term('takeaway'),
                'takeaway_hint' => $biz->term('takeaway_hint'),
                'takeaway_icon' => $biz->term('takeaway_icon'),
                'quick_sale' => $biz->term('quick_sale'),
            ],
            'categories' => ProductCategory::query()
                ->select(['id', 'name'])
                ->forCompany($companyId)
                ->orderBy('name')
                ->get(),
            'products' => Product::query()
                ->select(['id', 'name', 'sell_price', 'discount', 'current_stock', 'image', 'code', 'category_id'])
                ->forCompany($companyId)
                ->orderBy('name')
                ->get()
                ->map(fn (Product $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'price' => (int) $p->sell_price,
                    'discount' => (int) $p->discount,
                    'stock' => (int) ($p->current_stock ?? 0),
                    'image' => $p->imageUrl(),
                    'code' => $p->formattedCode(),
                    'category_id' => $p->category_id,
                ]),
            'customers' => Customer::query()
                ->select(['id', 'name', 'phone', 'address'])
                ->forCompany($companyId)
                ->orderBy('name')
                ->limit(2000)
                ->get(),
        ]);
    }

    public function sync(Request $request, OrderService $orders): JsonResponse
    {
        $data = $request->validate([
            'sales' => ['required', 'array', 'max:200'],
            'sales.*.uuid' => ['required', 'string', 'size:36'],
            'sales.*.type' => ['required', Rule::in(['takeaway', 'delivery'])],
            'sales.*.discount' => ['nullable', 'integer', 'min:0', 'max:100'],
            'sales.*.created_at' => ['nullable', 'date'],
            'sales.*.customer_id' => ['nullable', 'integer'],
            'sales.*.customer' => ['nullable', 'array'],
            'sales.*.customer.name' => ['nullable', 'string', 'max:255'],
            'sales.*.customer.phone' => ['nullable', 'string', 'max:32'],
            'sales.*.customer.address' => ['nullable', 'string', 'max:500'],
            'sales.*.delivery_address' => ['nullable', 'string', 'max:500'],
            'sales.*.items' => ['required', 'array', 'min:1', 'max:200'],
            'sales.*.items.*.product_id' => ['required', 'integer'],
            'sales.*.items.*.quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'sales.*.items.*.price' => ['required', 'integer', 'min:0'],
            'sales.*.items.*.discount' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $companyId = (int) $request->user()->companyId();
        $userId = (int) $request->user()->id;
        $results = [];

        foreach ($data['sales'] as $sale) {
            $results[] = $this->syncOne($sale, $companyId, $userId, $orders);
        }

        return response()->json(['results' => $results, 'synced_at' => now()->toIso8601String()]);
    }

    /** @return array{uuid:string,status:string,order_id?:int,message?:string} */
    private function syncOne(array $sale, int $companyId, int $userId, OrderService $orders): array
    {
        $uuid = $sale['uuid'];

        $existing = Order::query()->withTrashed()->where('offline_uuid', $uuid)->value('id');

        if ($existing) {
            return ['uuid' => $uuid, 'status' => 'duplicate', 'order_id' => (int) $existing];
        }

        try {
            $productIds = collect($sale['items'])->pluck('product_id')->map(fn ($id) => (int) $id)->unique();

            $products = Product::query()
                ->select(['id', 'name'])
                ->forCompany($companyId)
                ->whereIn('id', $productIds)
                ->get()
                ->keyBy('id');

            $missing = $productIds->diff($products->keys());

            if ($missing->isNotEmpty()) {
                return ['uuid' => $uuid, 'status' => 'error', 'message' => 'Mahsulot topilmadi: #'.$missing->implode(', #')];
            }

            $items = collect($sale['items'])->map(fn ($item) => [
                'product_id' => (int) $item['product_id'],
                'name' => $products[(int) $item['product_id']]->name,
                'price' => (int) $item['price'],
                'discount' => (int) ($item['discount'] ?? 0),
                'quantity' => (int) $item['quantity'],
            ])->values()->all();

            $customerId = $this->resolveCustomer($sale, $companyId);
            // Brauzer vaqtni o'z zonasi bilan yuboradi; bazaga ilova zonasida yoziladi.
            $createdAt = ! empty($sale['created_at'])
                ? Carbon::parse($sale['created_at'])->setTimezone(config('app.timezone'))
                : null;

            $order = $orders->createDirectOrder(
                $companyId,
                $userId,
                OrderTypeEnum::from($sale['type']),
                $items,
                (int) ($sale['discount'] ?? 0),
                $customerId,
                $sale['delivery_address'] ?? null,
                $uuid,
                $createdAt,
            );

            return ['uuid' => $uuid, 'status' => 'created', 'order_id' => $order->id];
        } catch (\Throwable $e) {
            report($e);

            return ['uuid' => $uuid, 'status' => 'error', 'message' => 'Saqlashda xatolik. Qayta urinib ko\'ring.'];
        }
    }

    private function resolveCustomer(array $sale, int $companyId): ?int
    {
        if (! empty($sale['customer_id'])) {
            $id = Customer::query()->forCompany($companyId)->whereKey((int) $sale['customer_id'])->value('id');

            if ($id) {
                return (int) $id;
            }
        }

        $new = $sale['customer'] ?? null;

        if (! is_array($new) || blank($new['name'] ?? null)) {
            return null;
        }

        $phone = Phone::normalize($new['phone'] ?? null);

        if ($phone) {
            $found = Customer::query()->forCompany($companyId)->where('phone', $phone)->value('id');

            if ($found) {
                return (int) $found;
            }
        }

        return Customer::create([
            'company_id' => $companyId,
            'name' => trim($new['name']),
            'phone' => $phone,
            'address' => trim((string) ($new['address'] ?? '')) ?: null,
        ])->id;
    }
}
