<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use App\Support\Phone;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Hisob')
                    ->columns(2)
                    ->schema([
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
                        Select::make('company_id')
                            ->label('Qaysi biznes xodimi')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Biznes egasi uchun bo\'sh qoldiring — u o\'z biznesi orqali kiradi.'),
                    ]),
            ]);
    }
}
