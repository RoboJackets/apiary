<?php

declare(strict_types=1);

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

/**
 * A Nova resource for devices.
 *
 * @extends \App\Nova\Resource<\App\Models\Device>
 */
class Device extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Device>
     */
    public static $model = \App\Models\Device::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'serial_number';

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Meetings';

    /**
     * The columns that should be searched.
     *
     * @var array<string>
     */
    public static $search = [
        'serial_number',
        'manufacturer',
        'model',
        'hardware_version',
        'bluetooth_firmware_version',
        'bluetooth_software_version',
        'bootloader_version',
        'application_version',
    ];

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * Get the displayable label of the resource.
     *
     * @psalm-pure
     */
    #[\Override]
    public static function label(): string
    {
        return 'Devices';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @psalm-pure
     */
    #[\Override]
    public static function singularLabel(): string
    {
        return 'Device';
    }

    /**
     * Get the URI key for the resource.
     *
     * @psalm-pure
     */
    #[\Override]
    public static function uriKey(): string
    {
        return 'devices';
    }

    /**
     * Get the fields displayed by the resource.
     */
    #[\Override]
    public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Manufacturer', 'manufacturer')
                ->sortable(),

            Text::make('Model', 'model')
                ->sortable(),

            ID::make('Serial Number', 'serial_number')->sortable(),

            Panel::make('Versions', [
                Text::make('Hardware', 'hardware_version')
                    ->sortable()
                    ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin'))
                    ->displayUsing([self::class, 'normalizeVersion']),

                Text::make('Bluetooth Firmware', 'bluetooth_firmware_version')
                    ->sortable()
                    ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin'))
                    ->displayUsing([self::class, 'normalizeVersion']),

                Text::make('Bluetooth Software', 'bluetooth_software_version')
                    ->sortable()
                    ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin'))
                    ->displayUsing([self::class, 'normalizeVersion']),

                Text::make('Bootloader', 'bootloader_version')
                    ->sortable()
                    ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin'))
                    ->displayUsing([self::class, 'normalizeVersion']),

                Text::make('Application', 'application_version')
                    ->sortable()
                    ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin'))
                    ->displayUsing([self::class, 'normalizeVersion']),
            ]),

            Number::make('Battery', 'battery_percentage')
                ->min(0)
                ->max(100)
                ->sortable()
                ->displayUsing(static fn (string $percentage): string => $percentage.'%'),

            Panel::make('Last Seen', [
                BelongsTo::make('User', 'lastSeenUser', User::class)
                    ->searchable(),

                DateTime::make('Last Seen At', 'last_seen_at')
                    ->sortable(),

                Text::make('IP Address', 'last_seen_ip_address')
                    ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin'))
                    ->onlyOnDetail(),
            ]),

            HasMany::make('Attendance', 'attendances', Attendance::class)
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-attendance')),

            self::metadataPanel(),
        ];
    }

    /**
     * Normalize a version string for display.
     *
     * @psalm-pure
     */
    public static function normalizeVersion(string $version): string
    {
        return 'v'.ltrim($version, 'V');
    }
}
