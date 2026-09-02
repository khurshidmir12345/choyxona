<?php

namespace App\Filament\Widgets;

use App\Casts\OrderStatusEnum;
use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

/** Oxirgi 30 kun: kunlik tushum (barcha bizneslar). */
class SalesChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Kunlik tushum, oxirgi 30 kun';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $from = now()->subDays(29)->startOfDay();

        $rows = Order::query()
            ->where('status', OrderStatusEnum::Done->value)
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as d, COALESCE(SUM(total_amount), 0) as s')
            ->groupBy('d')
            ->pluck('s', 'd');

        $labels = [];
        $data = [];

        for ($i = 0; $i < 30; $i++) {
            $day = $from->copy()->addDays($i);
            $labels[] = $day->format('d.m');
            $data[] = (int) ($rows[$day->toDateString()] ?? 0);
        }

        return [
            'datasets' => [[
                'label' => 'Tushum, so\'m',
                'data' => $data,
                'fill' => true,
                'tension' => 0.3,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
