<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Casts\OrderStatusEnum;
use App\Casts\OrderTypeEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('created_at')->label('Sana')->dateTime('d.m.Y H:i')->sortable(),
                TextColumn::make('company.name')->label('Biznes')->searchable()->sortable(),
                TextColumn::make('type')
                    ->label('Turi')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        OrderTypeEnum::Delivery => 'Yetkazib berish',
                        OrderTypeEnum::Takeaway => 'Olib ketish',
                        OrderTypeEnum::Cafe => 'Zalda',
                        default => '—',
                    })
                    ->color('gray'),
                TextColumn::make('status')
                    ->label('Holati')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        OrderStatusEnum::Done => 'Yopilgan',
                        OrderStatusEnum::Opened => 'Ochiq',
                        default => ucfirst((string) ($state?->value ?? $state)),
                    })
                    ->color(fn ($state) => $state === OrderStatusEnum::Done ? 'success' : 'warning'),
                TextColumn::make('total_amount')
                    ->label('Summa')
                    ->formatStateUsing(fn ($state) => number_format((int) $state, 0, ',', ' ').' so\'m')
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('customer.name')->label('Mijoz')->placeholder('—'),
                TextColumn::make('user.name')->label('Kassir')->placeholder('—'),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('company_id')
                    ->label('Biznes')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Holati')
                    ->options([
                        OrderStatusEnum::Done->value => 'Yopilgan',
                        OrderStatusEnum::Opened->value => 'Ochiq',
                    ]),
                Filter::make('period')
                    ->label('Davr')
                    ->schema([
                        DatePicker::make('from')->label('Sanadan'),
                        DatePicker::make('to')->label('Sanagacha'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($data['to'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))),
            ])
            ->emptyStateHeading('Sotuv yo\'q');
    }
}
