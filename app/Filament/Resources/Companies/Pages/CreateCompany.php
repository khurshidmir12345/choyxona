<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use App\Models\Company;
use App\Models\User;
use App\Support\Phone;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/** Biznes va uning egasi (kirish hisobi) bitta formada yaratiladi. */
class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    protected static ?string $title = 'Yangi biznes';

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $owner = User::create([
                'name' => $data['owner_name'],
                'phone_number' => Phone::normalize($data['owner_phone']),
                'password' => $data['owner_password'],
                'type' => 'owner',
            ]);

            unset($data['owner_name'], $data['owner_phone'], $data['owner_password']);

            return Company::create($data + ['user_id' => $owner->id, 'balance' => 0]);
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
