<?php

namespace App\Filament\Widgets;

use App\Casts\OrderStatusEnum;
use App\Models\Company;
use App\Models\Order;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Bosh sahifadagi asosiy raqamlar. */
class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Umumiy ko\'rsatkichlar';

    protected function getStats(): array
    {
        $done = OrderStatusEnum::Done->value;
        $monthStart = now()->startOfMonth();

        $today = Order::query()->where('status', $done)->whereDate('created_at', today())
            ->selectRaw('COUNT(*) as c, COALESCE(SUM(total_amount), 0) as s')->first();
        $month = Order::query()->where('status', $done)->where('created_at', '>=', $monthStart)
            ->selectRaw('COUNT(*) as c, COALESCE(SUM(total_amount), 0) as s')->first();

        $activeCompanies = Order::query()
            ->where('created_at', '>=', now()->subDays(7))
            ->distinct('company_id')
            ->count('company_id');

        $fmt = fn ($n) => number_format((int) $n, 0, ',', ' ');

        return [
            Stat::make('Bizneslar', Company::count())
                ->description($activeCompanies.' tasi oxirgi 7 kunda sotuv qilgan')
                ->descriptionIcon('heroicon-o-building-storefront')
                ->color('primary'),
            Stat::make('Foydalanuvchilar', User::count())
                ->description('Egalar va xodimlar')
                ->descriptionIcon('heroicon-o-users'),
            Stat::make('Bugun', $fmt($today->s).' so\'m')
                ->description($today->c.' ta sotuv')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),
            Stat::make('Shu oy', $fmt($month->s).' so\'m')
                ->description($month->c.' ta sotuv')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('success'),
        ];
    }
}
