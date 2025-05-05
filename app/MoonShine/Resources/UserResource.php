<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;

use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Password;
use MoonShine\UI\Fields\PasswordRepeat;
use MoonShine\UI\Fields\Phone;
use MoonShine\UI\Fields\Text;

/**
 * @extends ModelResource<User>
 */
class UserResource extends ModelResource
{
    protected string $model = User::class;

    protected string $title = 'Users';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Phone','phone_number')->badge('green'),
            Text::make('name'),
            Text::make('balance')->badge('warning'),
            Date::make('Verify','phone_verified_at')->format('d.m.Y'),
            BelongsTo::make('Role', 'role', formatted: static fn (Role $model) => $model->name)->badge('primary'),
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make([
                ID::make()->sortable(),
                Text::make('phone_number')->badge('green'),
                Text::make('name'),
                Text::make('balance'),
                Text::make('type'),
                Date::make('phone_verified_at'),
                BelongsTo::make('Role', 'role', 'name'),
                Password::make('Password'),
                PasswordRepeat::make('Password repeat'),
            ])
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function detailFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('phone_number')->badge('green'),
            Text::make('name'),
            Text::make('balance'),
            Text::make('type')->badge('warning'),
            Date::make('phone_verified_at'),
            BelongsTo::make('Role', 'role', formatted: static fn (Role $model) => $model->name),
            BelongsTo::make('Company', 'company', formatted: static fn (Company $model) => $model->name),
        ];
    }

    /**
     * @param User $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
