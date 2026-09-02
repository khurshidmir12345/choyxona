<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use App\Models\User;
use App\Support\Phone;
use Closure;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Biznes xodimlari (kassirlar): shu yerda hisob yaratiladi va o'chiriladi. */
class SellersRelationManager extends RelationManager
{
    protected static string $relationship = 'sellers';

    protected static ?string $title = 'Xodimlar (kassirlar)';

    protected static ?string $modelLabel = 'xodim';

    protected static ?string $pluralModelLabel = 'Xodimlar';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Ismi')
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone_number')
                    ->label('Telefon (login)')
                    ->tel()
                    ->required()
                    ->placeholder('90 123 45 67')
                    ->dehydrateStateUsing(fn ($state) => Phone::normalize($state))
                    ->rule(fn ($record): Closure => function (string $attribute, $value, Closure $fail) use ($record) {
                        $phone = Phone::normalize($value);

                        if (! $phone) {
                            $fail('Telefon raqamni kiriting.');

                            return;
                        }

                        $exists = User::withTrashed()
                            ->where('phone_number', $phone)
                            ->when($record, fn ($q) => $q->whereKeyNot($record->id))
                            ->exists();

                        if ($exists) {
                            $fail('Bu raqam boshqa foydalanuvchida bor.');
                        }
                    }),
                TextInput::make('password')
                    ->label(fn ($record) => $record ? 'Yangi parol (bo\'sh qoldirilsa o\'zgarmaydi)' : 'Parol')
                    ->password()
                    ->revealable()
                    ->required(fn ($record) => $record === null)
                    ->minLength(6)
                    ->dehydrated(fn ($state) => filled($state)),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->label('Ismi')->searchable(),
                TextColumn::make('phone_number')->label('Telefon')->searchable(),
                TextColumn::make('created_at')->label('Qo\'shilgan')->date('d.m.Y'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Xodim qo\'shish')
                    ->mutateDataUsing(fn (array $data) => $data + ['type' => 'seller']),
            ])
            ->recordActions([
                EditAction::make()->label('Tahrirlash'),
                DeleteAction::make()->label('O\'chirish'),
            ])
            ->emptyStateHeading('Xodim yo\'q')
            ->emptyStateDescription('Kassirlar uchun alohida hisob yarating.');
    }
}
