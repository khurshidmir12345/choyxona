<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Ismi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('phone_number')
                    ->label('Telefon (login)')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('role')
                    ->label('Roli')
                    ->badge()
                    ->state(fn ($record) => $record->ownedCompany ? 'Egasi' : 'Xodim')
                    ->color(fn ($state) => $state === 'Egasi' ? 'success' : 'gray'),
                TextColumn::make('business')
                    ->label('Biznes')
                    ->state(fn ($record) => $record->ownedCompany?->name ?? $record->company?->name ?? '—'),
                TextColumn::make('created_at')
                    ->label('Qo\'shilgan')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('deleted_at')
                    ->label('O\'chirilgan')
                    ->dateTime('d.m.Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('company_id')
                    ->label('Biznes')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),
                TrashedFilter::make()->label('O\'chirilganlar'),
            ])
            ->recordActions([
                EditAction::make()->label('Tahrirlash'),
                DeleteAction::make()->label('O\'chirish'),
                RestoreAction::make()->label('Tiklash'),
                ForceDeleteAction::make()->label('Butunlay o\'chirish'),
            ])
            ->emptyStateHeading('Foydalanuvchi yo\'q');
    }
}
