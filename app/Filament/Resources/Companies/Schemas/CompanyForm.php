<?php

namespace App\Filament\Resources\Companies\Schemas;

use App\Casts\BusinessType;
use App\Models\User;
use App\Support\Phone;
use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Biznes')
                    ->description('Nomi va turi. Tur menyu va so\'zlarni belgilaydi.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nomi')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Radio::make('business_type')
                            ->label('Turi')
                            ->options(BusinessType::class)
                            ->descriptions([
                                'cafe' => BusinessType::Cafe->description(),
                                'retail' => BusinessType::Retail->description(),
                            ])
                            ->default('cafe')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('phone_number')
                            ->label('Telefon')
                            ->tel()
                            ->maxLength(32),
                        TextInput::make('address')
                            ->label('Manzil')
                            ->maxLength(500),
                        FileUpload::make('logo')
                            ->label('Logotip')
                            ->image()
                            ->disk('public')
                            ->directory('company')
                            ->imageResizeTargetWidth('512')
                            ->imageResizeTargetHeight('512')
                            ->columnSpanFull(),
                    ]),

                Section::make('Egasi — tizimga kirish hisobi')
                    ->description('Shu telefon va parol bilan biznes egasi tizimga kiradi.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('owner_name')
                            ->label('Ismi')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('owner_phone')
                            ->label('Telefon (login)')
                            ->tel()
                            ->required()
                            ->placeholder('90 123 45 67')
                            ->rule(fn ($record): Closure => function (string $attribute, $value, Closure $fail) use ($record) {
                                $phone = Phone::normalize($value);

                                if (! $phone) {
                                    $fail('Telefon raqamni kiriting.');

                                    return;
                                }

                                $exists = User::withTrashed()
                                    ->where('phone_number', $phone)
                                    ->when($record?->user_id, fn ($q) => $q->whereKeyNot($record->user_id))
                                    ->exists();

                                if ($exists) {
                                    $fail('Bu raqam boshqa foydalanuvchida bor.');
                                }
                            }),
                        TextInput::make('owner_password')
                            ->label(fn ($record) => $record ? 'Yangi parol (bo\'sh qoldirilsa o\'zgarmaydi)' : 'Parol')
                            ->password()
                            ->revealable()
                            ->required(fn ($record) => $record === null)
                            ->minLength(6)
                            ->maxLength(72)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
