<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.EmptyStatement.DetectedCatch
// phpcs:disable Squiz.WhiteSpace.OperatorSpacing.NoSpaceAfter
// phpcs:disable Squiz.WhiteSpace.OperatorSpacing.NoSpaceBefore

namespace App\Nova\Actions;

use App\Models\DuesPackage;
use App\Models\FiscalYear;
use App\Models\Merchandise;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\MultipleRecordsFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Http\Requests\NovaRequest;

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
     * @param  \Illuminate\Support\Collection<int,\App\Models\FiscalYear>  $models
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        if (count($models) !== 1) {
            return Action::danger('This action can only be run on a single fiscal year at a time.');
        }

        $fiscalYear = $models->first();
        $createdPackages = 0;

        $startingYear = intval($fiscalYear->ending_year) - 1;
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
        $waiveShirt = Merchandise::firstOrCreate([
            'name' => 'Waive Fall '.$startingYear.' Merchandise',
            'fiscal_year_id' => $fiscalYear->id,
        ]);
        $waivePolo = Merchandise::firstOrCreate([
            'name' => 'Waive Spring '.$endingYear.' Merchandise',
            'fiscal_year_id' => $fiscalYear->id,
        ]);

        $fallPackageName = 'Fall '.$startingYear;
        $fallPackageCost = 55;
        $springPackageName = 'Spring '.$endingYear;
        $springPackageCost = 55;
        $studentFullYear = 'Full Year ('.$startingYear.'-'.$endingYear.')';
        $studentFullYearCost = 100;
        $nonStudentFullYear = 'Full Year ('.$startingYear.'-'.$endingYear.') - Non-Student';
        $nonStudentFullYearCost = 200;

        $fallEffectiveStart = Carbon::create($startingYear, 7, 1, 12, 0, 0, config('app.timezone'));
        $fallEffectiveEnd = Carbon::create($endingYear, 1, 1, 12, 0, 0, config('app.timezone'));
        $fallAccessStart = Carbon::create($startingYear, 7, 1, 12, 0, 0, config('app.timezone'));
        $fallAccessEnd = Carbon::create($endingYear, 1, 31, 12, 0, 0, config('app.timezone'));
        $springEffectiveStart = Carbon::create($endingYear, 1, 1, 12, 0, 0, config('app.timezone'));
        $springEffectiveEnd = Carbon::create($endingYear, 8, 1, 12, 0, 0, config('app.timezone'));
        $springAccessStart = Carbon::create($endingYear, 1, 1, 12, 0, 0, config('app.timezone'));
        $springAccessEnd = Carbon::create($endingYear, 9, 31, 12, 0, 0, config('app.timezone'));

        $previousFiscalYear = FiscalYear::whereEndingYear($startingYear)->first();

        if ($previousFiscalYear !== null) {
            try {
                $fallPackageCost = $previousFiscalYear
                    ->packages()
                    ->where('name', 'like', 'Fall%')
                    ->sole()
                    ->cost;
            } catch (MultipleRecordsFoundException|ModelNotFoundException) {
                // do nothing
            }

            try {
                $springPackageCost = $previousFiscalYear
                    ->packages()
                    ->where('name', 'like', 'Spring%')
                    ->sole()
                    ->cost;
            } catch (MultipleRecordsFoundException|ModelNotFoundException) {
                // do nothing
            }

            try {
                $studentFullYearCost = $previousFiscalYear
                    ->packages()
                    ->where('name', 'like', 'Full Year (%)')
                    ->sole()
                    ->cost;

                $nonStudentFullYearCost = $studentFullYearCost * 2;
            } catch (MultipleRecordsFoundException|ModelNotFoundException) {
                // do nothing
            }

            try {
                $nonStudentFullYearCost = $previousFiscalYear
                    ->packages()
                    ->where('name', 'like', 'Full Year (%) - Non-Student')
                    ->sole()
                    ->cost;
            } catch (MultipleRecordsFoundException|ModelNotFoundException) {
                // do nothing
            }
        }

        if (DuesPackage::where('name', $fallPackageName)->doesntExist()) {
            $duesPackage = new DuesPackage();
            $duesPackage->name = $fallPackageName;
            $duesPackage->effective_start = $fallEffectiveStart;
            $duesPackage->effective_end = $fallEffectiveEnd;
            $duesPackage->access_start = $fallAccessStart;
            $duesPackage->access_end = $fallAccessEnd;
            $duesPackage->cost = $fallPackageCost;
            $duesPackage->available_for_purchase = false;
            $duesPackage->fiscal_year_id = $fiscalYear->id;
            $duesPackage->restricted_to_students = true;
            $duesPackage->save();
            $duesPackage->merchandise()->attach($shirt, ['group' => 'Fall']);
            $duesPackage->merchandise()->attach($waiveShirt, ['group' => 'Fall']);

            $createdPackages++;
        }

        if (DuesPackage::where('name', $springPackageName)->doesntExist()) {
            $duesPackage = new DuesPackage();
            $duesPackage->name = $springPackageName;
            $duesPackage->effective_start = $springEffectiveStart;
            $duesPackage->effective_end = $springEffectiveEnd;
            $duesPackage->access_start = $springAccessStart;
            $duesPackage->access_end = $springAccessEnd;
            $duesPackage->cost = $springPackageCost;
            $duesPackage->available_for_purchase = false;
            $duesPackage->fiscal_year_id = $fiscalYear->id;
            $duesPackage->restricted_to_students = true;
            $duesPackage->save();
            $duesPackage->merchandise()->attach($polo, ['group' => 'Spring']);
            $duesPackage->merchandise()->attach($waivePolo, ['group' => 'Spring']);

            $createdPackages++;
        }

        if (DuesPackage::where('name', $studentFullYear)->doesntExist()) {
            $duesPackage = new DuesPackage();
            $duesPackage->name = $studentFullYear;
            $duesPackage->effective_start = $fallEffectiveStart;
            $duesPackage->effective_end = $springEffectiveEnd;
            $duesPackage->access_start = $fallAccessStart;
            $duesPackage->access_end = $springAccessEnd;
            $duesPackage->cost = $studentFullYearCost;
            $duesPackage->available_for_purchase = false;
            $duesPackage->fiscal_year_id = $fiscalYear->id;
            $duesPackage->restricted_to_students = true;
            $duesPackage->conflicts_with_package_id = DuesPackage::where('name', $fallPackageName)->firstOrFail()->id;
            $duesPackage->save();
            $duesPackage->merchandise()->attach($shirt, ['group' => 'Fall']);
            $duesPackage->merchandise()->attach($waiveShirt, ['group' => 'Fall']);
            $duesPackage->merchandise()->attach($polo, ['group' => 'Spring']);
            $duesPackage->merchandise()->attach($waivePolo, ['group' => 'Spring']);

            $createdPackages++;
        }

        if (DuesPackage::where('name', $nonStudentFullYear)->doesntExist() && $fields->non_student === true) {
            $duesPackage = new DuesPackage();
            $duesPackage->name = $nonStudentFullYear;
            $duesPackage->effective_start = $fallEffectiveStart;
            $duesPackage->effective_end = $springEffectiveEnd;
            $duesPackage->access_start = $fallAccessStart;
            $duesPackage->access_end = $springAccessEnd;
            $duesPackage->cost = $nonStudentFullYearCost;
            $duesPackage->available_for_purchase = false;
            $duesPackage->fiscal_year_id = $fiscalYear->id;
            $duesPackage->restricted_to_students = false;
            $duesPackage->save();
            // Non-students don't get the merch options

            $createdPackages++;
        }

        if ($createdPackages === 0) {
            return Action::message('All packages already exist; no changes were made.');
        }

        return Action::message('Created '.$createdPackages.' '.Str::plural('package', $createdPackages).'!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Boolean::make('Non-Student Package', 'non_student')
                ->help('Whether to create a package for non-students. This should generally be left checked.')
                ->default(true),
        ];
    }
}
