<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\DuesPackage;
use App\Models\Merchandise;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Boolean;

class CreateDuesPackages extends Action
{
    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;

    /**
     * Perform the action on the given models.
     *
     * @param \Illuminate\Support\Collection<\App\Models\FiscalYear>  $models
     *
     * @return array<string,string>
     */
    public function handle(ActionFields $fields, Collection $models): array
    {
        if (1 !== count($models)) {
            return Action::danger('This action can only be run on a single fiscal year at a time.');
        }

        $fiscalYear = $models->first();
        $createdPackages = 0;

        $startingYear = $fiscalYear->ending_year - 1;
        $endingYear = $fiscalYear->ending_year;
        $yearRangeStr = $startingYear.'-'.$endingYear;

        $shirt = Merchandise::firstOrCreate([
            'name' => 'T-Shirt '.$yearRangeStr,
            'fiscal_year_id' => $fiscalYear->id,
        ]);
        $polo = Merchandise::firstOrCreate([
            'name' => 'Polo '.$yearRangeStr,
            'fiscal_year_id' => $fiscalYear->id,
        ]);

        $fallPackageName = 'Fall '.$startingYear;
        $springPackageName = 'Spring '.$endingYear;
        $studentFullYear = 'Full Year ('.$startingYear.'-'.$endingYear.')';
        $nonStudentFullYear = 'Full Year ('.$startingYear.'-'.$endingYear.') - Non-Student';

        $fallEffectiveStart = Carbon::create($startingYear, 8, 1, 12, 0, 0, config('app.timezone'));
        $fallEffectiveEnd = Carbon::create($startingYear, 12, 31, 12, 0, 0, config('app.timezone'));
        $fallAccessStart = Carbon::create($startingYear, 8, 1, 12, 0, 0, config('app.timezone'));
        $fallAccessEnd = Carbon::create($endingYear, 1, 31, 12, 0, 0, config('app.timezone'));
        $springEffectiveStart = Carbon::create($endingYear, 1, 1, 12, 0, 0, config('app.timezone'));
        $springEffectiveEnd = Carbon::create($endingYear, 8, 1, 12, 0, 0, config('app.timezone'));
        $springAccessStart = Carbon::create($endingYear, 1, 1, 12, 0, 0, config('app.timezone'));
        $springAccessEnd = Carbon::create($endingYear, 9, 31, 12, 0, 0, config('app.timezone'));

        if (DuesPackage::where('name', $fallPackageName)->doesntExist()) {
            $duesPackage = new DuesPackage();
            $duesPackage->name = $fallPackageName;
            $duesPackage->effective_start = $fallEffectiveStart;
            $duesPackage->effective_end = $fallEffectiveEnd;
            $duesPackage->access_start = $fallAccessStart;
            $duesPackage->access_end = $fallAccessEnd;
            $duesPackage->cost = 55;
            $duesPackage->available_for_purchase = false;
            $duesPackage->fiscal_year_id = $fiscalYear->id;
            $duesPackage->restricted_to_students = true;
            $duesPackage->save();
            $duesPackage->merchandise()->attach($shirt, ['group' => 'Fall']);

            $createdPackages++;
        }

        if (DuesPackage::where('name', $springPackageName)->doesntExist()) {
            $duesPackage = new DuesPackage();
            $duesPackage->name = $springPackageName;
            $duesPackage->effective_start = $springEffectiveStart;
            $duesPackage->effective_end = $springEffectiveEnd;
            $duesPackage->access_start = $springAccessStart;
            $duesPackage->access_end = $springAccessEnd;
            $duesPackage->cost = 55;
            $duesPackage->available_for_purchase = false;
            $duesPackage->fiscal_year_id = $fiscalYear->id;
            $duesPackage->restricted_to_students = true;
            $duesPackage->save();
            $duesPackage->merchandise()->attach($polo, ['group' => 'Spring']);

            $createdPackages++;
        }

        if (DuesPackage::where('name', $studentFullYear)->doesntExist()) {
            $duesPackage = new DuesPackage();
            $duesPackage->name = $studentFullYear;
            $duesPackage->effective_start = $fallEffectiveStart;
            $duesPackage->effective_end = $springEffectiveEnd;
            $duesPackage->access_start = $fallAccessStart;
            $duesPackage->access_end = $springAccessEnd;
            $duesPackage->cost = 100;
            $duesPackage->available_for_purchase = false;
            $duesPackage->fiscal_year_id = $fiscalYear->id;
            $duesPackage->restricted_to_students = true;
            $duesPackage->conflicts_with_package_id = DuesPackage::where(
                'name',
                $fallPackageName
            )->firstOrFail()->id;
            $duesPackage->save();
            $duesPackage->merchandise()->attach($shirt, ['group' => 'Fall']);
            $duesPackage->merchandise()->attach($polo, ['group' => 'Spring']);

            $createdPackages++;
        }

        if (DuesPackage::where('name', $nonStudentFullYear)->doesntExist() && true === $fields->non_student) {
            $duesPackage = new DuesPackage();
            $duesPackage->name = $nonStudentFullYear;
            $duesPackage->effective_start = $fallEffectiveStart;
            $duesPackage->effective_end = $springEffectiveEnd;
            $duesPackage->access_start = $fallAccessStart;
            $duesPackage->access_end = $springAccessEnd;
            $duesPackage->cost = 200;
            $duesPackage->available_for_purchase = true;
            $duesPackage->fiscal_year_id = $fiscalYear->id;
            $duesPackage->restricted_to_students = false;
            $duesPackage->save();
            // Non-students don't get the merch options

            $createdPackages++;
        }

        if (0 === $createdPackages) {
            return Action::message('All packages already exist; no changes were made.');
        }

        return Action::message('Created '.$createdPackages.' '.Str::plural('package', $createdPackages).'!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(): array
    {
        return [
            Boolean::make('Non-Student Package', 'non_student')
                ->help('Whether to create a package for non-students. This should generally be left checked.')
                ->default(true),
        ];
    }
}
