<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use App\Models\User;
use App\Support\Phone;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('O\'chirish'),
            RestoreAction::make()->label('Tiklash'),
            ForceDeleteAction::make()->label('Butunlay o\'chirish'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $owner = $this->record->user;

        $data['owner_name'] = $owner?->name;
        $data['owner_phone'] = $owner?->phone_number;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            $ownerData = [
                'name' => $data['owner_name'],
                'phone_number' => Phone::normalize($data['owner_phone']),
            ];

            if (filled($data['owner_password'] ?? null)) {
                $ownerData['password'] = $data['owner_password'];
            }

            $owner = $record->user;

            if ($owner) {
                $owner->update($ownerData);
            } else {
                $owner = User::create($ownerData + ['password' => $data['owner_password'] ?? str()->random(12), 'type' => 'owner']);
                $data['user_id'] = $owner->id;
            }

            unset($data['owner_name'], $data['owner_phone'], $data['owner_password']);

            $record->update($data);

            return $record;
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
