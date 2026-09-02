<?php

namespace App\Filament\Widgets;

use App\Casts\OrderStatusEnum;
use App\Models\Company;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/** Shu oyda eng ko'p sotgan bizneslar. */
class TopCompanies extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Shu oyda eng faol bizneslar';

    public function table(Table $table): Table
    {
        $thisMonth = fn ($q) => $q
            ->where('status', OrderStatusEnum::Done->value)
            ->where('created_at', '>=', now()->startOfMonth());

        return $table
            ->query(
                Company::query()
                    ->withCount(['orders as month_orders' => $thisMonth])
                    ->withSum(['orders as month_revenue' => $thisMonth], 'total_amount')
                    ->orderByDesc('month_revenue')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('name')->label('Biznes')->weight('bold'),
                TextColumn::make('business_type')->label('Turi')->badge()->color('gray'),
                TextColumn::make('month_orders')->label('Sotuvlar')->alignCenter(),
                TextColumn::make('month_revenue')
                    ->label('Tushum')
                    ->formatStateUsing(fn ($state) => number_format((int) $state, 0, ',', ' ').' so\'m')
                    ->alignEnd(),
            ])
            ->paginated(false);
    }
}
