<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Yangi foydalanuvchi';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = filled($data['company_id'] ?? null) ? 'seller' : 'owner';

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
