<?php

namespace App\Filament\Resources\Companies\Tables;

use App\Casts\BusinessType;
use App\Casts\OrderStatusEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        $done = fn ($query) => $query->where('status', OrderStatusEnum::Done->value);

        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Biznes')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->address),
                TextColumn::make('business_type')
                    ->label('Turi')
                    ->badge()
                    ->color(fn ($state) => $state === BusinessType::Cafe ? 'warning' : 'info'),
                TextColumn::make('user.name')
                    ->label('Egasi')
                    ->searchable()
                    ->description(fn ($record) => $record->user?->phone_number),
                TextColumn::make('sellers_count')
                    ->label('Xodimlar')
                    ->counts('sellers')
                    ->alignCenter(),
                TextColumn::make('orders_count')
                    ->label('Sotuvlar')
                    ->counts(['orders' => $done])
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('orders_sum_total_amount')
                    ->label('Tushum')
                    ->sum(['orders' => $done], 'total_amount')
                    ->formatStateUsing(fn ($state) => number_format((int) $state, 0, ',', ' ').' so\'m')
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Qo\'shilgan')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('deleted_at')
                    ->label('O\'chirilgan')
                    ->dateTime('d.m.Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('business_type')
                    ->label('Turi')
                    ->options(BusinessType::class),
                TrashedFilter::make()->label('O\'chirilganlar'),
            ])
            ->recordActions([
                EditAction::make()->label('Tahrirlash'),
                DeleteAction::make()
                    ->label('O\'chirish')
                    ->modalHeading('Biznes o\'chirilsinmi?')
                    ->modalDescription('Egasi va xodimlari tizimga kira olmaydi. Ma\'lumotlar saqlanib qoladi, tiklash mumkin.'),
                RestoreAction::make()->label('Tiklash'),
                ForceDeleteAction::make()
                    ->label('Butunlay o\'chirish')
                    ->modalDescription('Biznes barcha ma\'lumotlari bilan qaytarib bo\'lmaydigan tarzda o\'chiriladi.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('O\'chirish'),
                ]),
            ])
            ->emptyStateHeading('Hali biznes yo\'q')
            ->emptyStateDescription('"Yangi biznes" tugmasi bilan birinchi biznesni va uning egasini yarating.');
    }
}
