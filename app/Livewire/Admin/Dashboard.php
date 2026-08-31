<?php

namespace App\Livewire\Admin;

use App\Casts\OrderStatusEnum;
use App\Livewire\Concerns\WithCompany;
use App\Models\Expense;
use App\Models\Order;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * Savdo paneli.
 *
 * Hisob-kitoblarning hammasi SQL tomonida. Ilgari davr ichidagi barcha
 * buyurtmalar mahsulotlari bilan xotiraga yuklanardi, grafik esa har bir kun
 * uchun ikkita alohida so'rov qilardi (bir oy = 60+ so'rov).
 */
class Dashboard extends Component
{
    use WithCompany;

    public const PERIODS = [
        'today' => 'Bugun',
        'yesterday' => 'Kecha',
        'week' => 'Shu hafta',
        'month' => 'Shu oy',
        'last_month' => 'O\'tgan oy',
        'custom' => 'Boshqa davr',
    ];

    public string $selectedPeriod = 'month';

    public string $startDate = '';

    public string $endDate = '';

    public function mount(): void
    {
        $this->applyPeriod();
    }

    public function updatedSelectedPeriod(): void
    {
        $this->applyPeriod();
    }

    public function updatedStartDate(): void
    {
        $this->selectedPeriod = 'custom';
    }

    public function updatedEndDate(): void
    {
        $this->selectedPeriod = 'custom';
    }

    private function applyPeriod(): void
    {
        $now = CarbonImmutable::now();

        [$start, $end] = match ($this->selectedPeriod) {
            'today' => [$now->startOfDay(), $now->endOfDay()],
            'yesterday' => [$now->subDay()->startOfDay(), $now->subDay()->endOfDay()],
            'week' => [$now->startOfWeek(), $now->endOfWeek()],
            'last_month' => [$now->subMonth()->startOfMonth(), $now->subMonth()->endOfMonth()],
            'custom' => [null, null],
            default => [$now->startOfMonth(), $now->endOfMonth()],
        };

        if ($start && $end) {
            $this->startDate = $start->format('Y-m-d');
            $this->endDate = $end->format('Y-m-d');
        }
    }

    /** @return array{0: CarbonImmutable, 1: CarbonImmutable} */
    private function range(): array
    {
        $start = rescue(fn () => CarbonImmutable::parse($this->startDate), CarbonImmutable::now()->startOfMonth(), false);
        $end = rescue(fn () => CarbonImmutable::parse($this->endDate), CarbonImmutable::now()->endOfMonth(), false);

        if ($end->lessThan($start)) {
            [$start, $end] = [$end, $start];
        }

        return [$start->startOfDay(), $end->endOfDay()];
    }

    public function render()
    {
        [$start, $end] = $this->range();
        $companyId = $this->companyId();

        $sales = Order::query()
            ->forCompany($companyId)
            ->done()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('COUNT(*) as orders_count, COALESCE(SUM(total_amount), 0) as revenue')
            ->first();

        $revenue = (int) $sales->revenue;
        $ordersCount = (int) $sales->orders_count;

        // Foyda: sotuvdan tushgan pul minus sotilgan mahsulotlar tannarxi.
        // Narx tarixiy (order_details.total_amount), tannarx joriy (products.price).
        $cost = (int) DB::table('order_details')
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->join('products', 'products.id', '=', 'order_details.product_id')
            ->where('orders.company_id', $companyId)
            ->where('orders.status', OrderStatusEnum::Done->value)
            ->whereNull('orders.deleted_at')
            ->whereNull('order_details.deleted_at')
            ->whereBetween('orders.created_at', [$start, $end])
            ->sum(DB::raw('order_details.quantity * COALESCE(products.price, 0)'));

        $profit = $revenue - $cost;

        $expenses = (float) Expense::query()
            ->forCompany($companyId)
            ->where('status', 'approved')
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');

        $days = max(1, $start->diffInDays($end) + 1);

        return view('livewire.admin.dashboard', [
            'revenue' => $revenue,
            'ordersCount' => $ordersCount,
            'profit' => $profit,
            'expenses' => $expenses,
            'netProfit' => $profit - $expenses,
            'profitMargin' => $revenue > 0 ? round($profit / $revenue * 100, 1) : 0,
            'averageCheck' => $ordersCount > 0 ? (int) round($revenue / $ordersCount) : 0,
            'dailyAverage' => (int) round($revenue / $days),
            'chartData' => $this->chartData($companyId, $start, $end),
            'topProducts' => $this->topProducts($companyId, $start, $end),
            'topCategories' => $this->topCategories($companyId, $start, $end),
            'ordersByType' => $this->ordersByType($companyId, $start, $end),
        ]);
    }

    /**
     * Sotuv va xarajat dinamikasi. Davr 62 kundan qisqa bo'lsa kunlik,
     * aks holda oylik. Ikkala qator ham bittadan guruhlangan so'rov.
     */
    private function chartData(?int $companyId, CarbonImmutable $start, CarbonImmutable $end): array
    {
        $days = $start->diffInDays($end) + 1;
        $daily = $days <= 62;

        $bucketOf = function (string $column) use ($daily): string {
            $pattern = $daily ? '%Y-%m-%d' : '%Y-%m';

            return DB::connection()->getDriverName() === 'sqlite'
                ? "strftime('{$pattern}', {$column})"
                : "DATE_FORMAT({$column}, '{$pattern}')";
        };

        $salesByBucket = Order::query()
            ->forCompany($companyId)
            ->done()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw($bucketOf('created_at').' as bucket')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as value')
            ->groupBy('bucket')
            ->pluck('value', 'bucket');

        $expensesByBucket = Expense::query()
            ->forCompany($companyId)
            ->where('status', 'approved')
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw($bucketOf('expense_date').' as bucket')
            ->selectRaw('COALESCE(SUM(amount), 0) as value')
            ->groupBy('bucket')
            ->pluck('value', 'bucket');

        $points = [];
        $cursor = $daily ? $start : $start->startOfMonth();

        while ($cursor->lessThanOrEqualTo($end)) {
            $key = $cursor->format($daily ? 'Y-m-d' : 'Y-m');

            $points[] = [
                'label' => $cursor->format($daily ? 'd.m' : 'm.Y'),
                'sales' => (float) ($salesByBucket[$key] ?? 0),
                'expenses' => (float) ($expensesByBucket[$key] ?? 0),
            ];

            $cursor = $daily ? $cursor->addDay() : $cursor->addMonth();
        }

        return $points;
    }

    private function topProducts(?int $companyId, CarbonImmutable $start, CarbonImmutable $end): array
    {
        return DB::table('order_details')
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->join('products', 'products.id', '=', 'order_details.product_id')
            ->where('orders.company_id', $companyId)
            ->where('orders.status', OrderStatusEnum::Done->value)
            ->whereNull('orders.deleted_at')
            ->whereNull('order_details.deleted_at')
            ->whereBetween('orders.created_at', [$start, $end])
            ->groupBy('products.id', 'products.name')
            ->select('products.name')
            ->selectRaw('SUM(order_details.quantity) as quantity')
            ->selectRaw('SUM(order_details.total_amount) as revenue')
            ->orderByDesc('revenue')
            ->limit(7)
            ->get()
            ->all();
    }

    private function topCategories(?int $companyId, CarbonImmutable $start, CarbonImmutable $end): array
    {
        return DB::table('order_details')
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->join('products', 'products.id', '=', 'order_details.product_id')
            ->join('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->where('orders.company_id', $companyId)
            ->where('orders.status', OrderStatusEnum::Done->value)
            ->whereNull('orders.deleted_at')
            ->whereNull('order_details.deleted_at')
            ->whereBetween('orders.created_at', [$start, $end])
            ->groupBy('product_categories.id', 'product_categories.name')
            ->select('product_categories.name')
            ->selectRaw('SUM(order_details.quantity) as quantity')
            ->selectRaw('SUM(order_details.total_amount) as revenue')
            ->orderByDesc('revenue')
            ->limit(7)
            ->get()
            ->all();
    }

    private function ordersByType(?int $companyId, CarbonImmutable $start, CarbonImmutable $end): array
    {
        return Order::query()
            ->forCompany($companyId)
            ->done()
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('type')
            ->select('type')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as revenue')
            ->get()
            ->all();
    }
}
