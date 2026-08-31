<?php

namespace App\Services;

use App\Casts\OrderStatusEnum;
use App\Casts\OrderTypeEnum;
use App\Casts\PlaceStatusEnum;
use App\Casts\ProductStockType;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Place;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;

/**
 * Buyurtma bo'yicha barcha yozish amallari shu yerda.
 *
 * Ilgari bu mantiq ikkita Livewire komponentida (zal va yetkazib berish)
 * ikki xil qilib takrorlangan edi: biri zaxirani kamaytirardi, ikkinchisi yo'q;
 * biri mahsulot chegirmasini hisoblardi, ikkinchisi tashlab ketardi.
 */
class OrderService
{
    /**
     * Savatdagi qatorlar shakli:
     * ['product_id' => int, 'name' => string, 'price' => int, 'discount' => int, 'quantity' => int]
     */
    public function openTableOrder(Place $place, int $companyId, int $userId): Order
    {
        return DB::transaction(function () use ($place, $companyId, $userId) {
            $order = Order::create([
                'company_id' => $companyId,
                'place_id' => $place->id,
                'user_id' => $userId,
                'type' => OrderTypeEnum::Cafe,
                'status' => OrderStatusEnum::Opened,
                'amount' => 0,
                'total_amount' => 0,
                'discount' => 0,
            ]);

            $place->update(['status' => PlaceStatusEnum::Busy]);

            return $order;
        });
    }

    /**
     * Ochiq buyurtma tarkibini savatga moslashtiradi va summalarni yangilaydi.
     * Zaxira bu bosqichda tegilmaydi — hisob yopilgandagina yechiladi.
     */
    public function syncItems(Order $order, array $items, int $orderDiscount, int $workerId): Order
    {
        return DB::transaction(function () use ($order, $items, $orderDiscount, $workerId) {
            $order->orderDetails()->forceDelete();

            $rows = [];
            $amount = 0;
            $now = now();

            foreach ($items as $item) {
                $line = $this->lineTotal($item);
                $amount += $line;

                $rows[] = [
                    'order_id' => $order->id,
                    'product_id' => (int) $item['product_id'],
                    'worker_id' => $workerId,
                    'quantity' => (int) $item['quantity'],
                    'price' => (int) $item['price'],
                    'discount' => (int) ($item['discount'] ?? 0),
                    'total_amount' => $line,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($rows !== []) {
                OrderDetail::insert($rows);
            }

            $orderDiscount = max(0, min(100, $orderDiscount));

            $order->update([
                'amount' => $amount,
                'discount' => $orderDiscount,
                'total_amount' => $this->applyDiscount($amount, $orderDiscount),
            ]);

            return $order->refresh();
        });
    }

    /**
     * Yetkazib berish / olib ketish: buyurtma bir urinishda ochiladi va yopiladi.
     */
    public function createDirectOrder(
        int $companyId,
        int $userId,
        OrderTypeEnum $type,
        array $items,
        int $orderDiscount
    ): Order {
        return DB::transaction(function () use ($companyId, $userId, $type, $items, $orderDiscount) {
            $amount = 0;
            foreach ($items as $item) {
                $amount += $this->lineTotal($item);
            }

            $orderDiscount = max(0, min(100, $orderDiscount));

            $order = Order::create([
                'company_id' => $companyId,
                'place_id' => null,
                'user_id' => $userId,
                'amount' => $amount,
                'total_amount' => $this->applyDiscount($amount, $orderDiscount),
                'discount' => $orderDiscount,
                'type' => $type,
                'status' => OrderStatusEnum::Done,
            ]);

            $this->syncItems($order, $items, $orderDiscount, $userId);
            $this->writeOffStock($order, $companyId);

            return $order->refresh();
        });
    }

    /**
     * Stoldagi hisobni yopadi: tarkibni saqlaydi, zaxirani yechadi,
     * joyni bo'shatadi.
     */
    public function closeTableOrder(Order $order, array $items, int $orderDiscount, int $workerId): Order
    {
        return DB::transaction(function () use ($order, $items, $orderDiscount, $workerId) {
            $this->syncItems($order, $items, $orderDiscount, $workerId);

            $order->update(['status' => OrderStatusEnum::Done]);
            $this->writeOffStock($order, (int) $order->company_id);
            $this->releasePlace($order);

            return $order->refresh();
        });
    }

    /** Ochiq buyurtmani bekor qiladi va stolni bo'shatadi. */
    public function cancelTableOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->orderDetails()->forceDelete();
            $this->releasePlace($order);
            $order->delete();
        });
    }

    /** Buyurtmani arxivga oladi; yopilgan bo'lsa zaxirani qaytaradi. */
    public function trashOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            if ($order->status === OrderStatusEnum::Done) {
                $this->restoreStock($order, (int) $order->company_id);
            }

            $this->releasePlace($order);
            $order->orderDetails()->delete();
            $order->delete();
        });
    }

    /** Arxivdagi buyurtmani tiklaydi va zaxirani qayta yechadi. */
    public function restoreOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->orderDetails()->withTrashed()->restore();
            $order->restore();

            if ($order->status === OrderStatusEnum::Done) {
                $this->writeOffStock($order, (int) $order->company_id);
            }
        });
    }

    public function forceDeleteOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->orderDetails()->withTrashed()->forceDelete();
            $order->forceDelete();
        });
    }

    /** Bitta qator uchun chegirmadan keyingi summa. */
    public function lineTotal(array $item): int
    {
        $gross = (int) $item['price'] * (int) $item['quantity'];
        $discount = max(0, min(100, (int) ($item['discount'] ?? 0)));

        return (int) round($gross - ($gross * $discount / 100));
    }

    public function applyDiscount(int $amount, int $discountPercent): int
    {
        $discountPercent = max(0, min(100, $discountPercent));

        return max(0, (int) round($amount - ($amount * $discountPercent / 100)));
    }

    /**
     * Sotilgan mahsulotlarni zaxiradan yechadi va harakatni product_stocks'ga
     * yozadi — shunda kirim/chiqim tarixi to'liq bo'ladi.
     */
    private function writeOffStock(Order $order, int $companyId): void
    {
        $this->moveStock($order, $companyId, ProductStockType::Sell, -1);
    }

    private function restoreStock(Order $order, int $companyId): void
    {
        $this->moveStock($order, $companyId, ProductStockType::Add, 1);
    }

    private function moveStock(Order $order, int $companyId, ProductStockType $type, int $sign): void
    {
        $details = $order->orderDetails()
            ->select(['product_id', 'quantity'])
            ->get()
            ->groupBy('product_id');

        if ($details->isEmpty()) {
            return;
        }

        $now = now();
        $movements = [];

        foreach ($details as $productId => $lines) {
            $quantity = (int) $lines->sum('quantity');

            Product::query()
                ->whereKey($productId)
                ->update(['current_stock' => DB::raw('COALESCE(current_stock, 0) + '.($sign * $quantity))]);

            $movements[] = [
                'company_id' => $companyId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'type' => $type->value,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        ProductStock::insert($movements);
    }

    private function releasePlace(Order $order): void
    {
        if (! $order->place_id) {
            return;
        }

        $stillBusy = Order::query()
            ->where('place_id', $order->place_id)
            ->whereKeyNot($order->id)
            ->opened()
            ->exists();

        if (! $stillBusy) {
            Place::query()
                ->whereKey($order->place_id)
                ->update(['status' => PlaceStatusEnum::Empty->value]);
        }
    }
}
