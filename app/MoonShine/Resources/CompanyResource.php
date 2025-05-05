<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use Illuminate\Database\Eloquent\Model;
use App\Models\Company;

use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Text;

/**
 * @extends ModelResource<Company>
 */
class CompanyResource extends ModelResource
{
    protected string $model = Company::class;

    protected string $title = 'Companies';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('name')->sortable(),
            BelongsTo::make('User', 'user', 'name')->searchable()->badge('green'),
            Text::make('Phone', 'phone_number')->sortable(),
            Text::make('Balance', 'balance')->badge('success'),
            Text::make('Address', 'address'),
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('name')->sortable(),
            BelongsTo::make('User', 'user', 'name')->searchable(),
            Text::make('Phone', 'phone_number')->sortable(),
            Text::make('Balance', 'balance')->sortable(),
            Text::make('Address', 'address')->sortable(),
            Text::make('Latitude', 'latitude'),
            Text::make('Longitude', 'longitude'),
            Text::make('Open time', 'open_time'),
            Text::make('Close time', 'close_time'),
            Image::make('Logo', 'logo')
                ->dir('logos')
                ->disk('public')
                ->required(),
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function detailFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('name')->sortable(),
            BelongsTo::make('User', 'user', 'name')->searchable(),
            Text::make('Phone', 'phone_number')->sortable(),
            Text::make('Balance', 'balance')->sortable(),
            Text::make('Address', 'address')->sortable(),
            Text::make('Latitude', 'latitude'),
            Text::make('Longitude', 'longitude'),
            Text::make('Open time', 'open_time'),
            Text::make('Close time', 'close_time'),
            Image::make('Logo', 'logo')
                ->dir('logos')
                ->disk('public')
                ->required(),
        ];
    }

    /**
     * @param Company $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
