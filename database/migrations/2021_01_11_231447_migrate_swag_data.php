<?php

declare(strict_types=1);

use App\Models\DuesTransaction;
use App\Models\FiscalYear;
use App\Models\Merchandise;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Migrations\Migration;

class MigrateSwagData extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        FiscalYear::get()->each(static function (FiscalYear $fy): void {
            if (0 === $fy->packages()->count()) {
                return;
            }

            $startingYear = $fy->ending_year - 1;
            $endingYear = $fy->ending_year;
            $yearRangeStr = $startingYear.'-'.$endingYear;

            $shirt = Merchandise::firstOrCreate([
                'name' => 'T-Shirt '.$yearRangeStr,
                'fiscal_year_id' => $fy->id,
            ]);
            $polo = Merchandise::firstOrCreate([
                'name' => 'Polo'.$yearRangeStr,
                'fiscal_year_id' => $fy->id,
            ]);

            $fallPackageName = 'Fall '.$startingYear;
            $springPackageName = 'Spring '.$endingYear;
            $studentFullYearName = 'Full Year ('.$startingYear.'-'.$endingYear.')';

            $fallPackage = $fy->packages()->where('name', $fallPackageName)->first();
            $springPackage = $fy->packages()->where('name', $springPackageName)->first();
            $studentFullYearPackage = $fy->packages()->where('name', $studentFullYearName)->first();

            // This ignores the swag eligibility columns and instead assumes that fall==shirt and spring==polo.
            // In practice that assumption is correct at the time this migration will be run.
            if (null !== $fallPackage) {
                $fallPackage->merchandise()->attach($shirt, ['group' => 'Fall']);
            }
            if (null !== $springPackage) {
                $springPackage->merchandise()->attach($polo, ['group' => 'Spring']);
            }
            if (null !== $studentFullYearPackage) {
                $studentFullYearPackage->merchandise()->attach($shirt, ['group' => 'Fall']);
                $studentFullYearPackage->merchandise()->attach($polo, ['group' => 'Spring']);
            }

            DuesTransaction::whereHas('package', static function (Builder $q) use ($fy): void {
                $q->where('fiscal_year_id', $fy->id);
            })->each(static function (DuesTransaction $dt) use ($shirt, $polo): void {
                // phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed
                if (null !== $dt->swag_shirt_provided) {
                    $dt->merchandise()->attach($shirt, [
                        'provided_at' => $dt->swag_shirt_provided,
                        'provided_by' => $dt->swag_shirt_providedBy,
                    ]);
                } elseif ($dt->package->eligible_for_shirt) {
                    $dt->merchandise()->attach($shirt);
                }

                if (null !== $dt->swag_polo_provided) {
                    $dt->merchandise()->attach($polo, [
                        'provided_at' => $dt->swag_polo_provided,
                        'provided_by' => $dt->swag_polo_providedBy,
                    ]);
                } elseif ($dt->package->eligible_for_polo) {
                    $dt->merchandise()->attach($polo);
                }
                // phpcs:enable
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        FiscalYear::get()->each(static function (FiscalYear $fy): void {
            $startingYear = $fy->ending_year - 1;
            $endingYear = $fy->ending_year;
            $yearRangeStr = $startingYear.'-'.$endingYear;

            $shirt = Merchandise::firstOrCreate([
                'name' => 'T-Shirt '.$yearRangeStr,
                'fiscal_year_id' => $fy->id,
            ]);
            $polo = Merchandise::firstOrCreate([
                'name' => 'Polo'.$yearRangeStr,
                'fiscal_year_id' => $fy->id,
            ]);

            DuesTransaction::get()->each(static function (DuesTransaction $dt) use ($shirt, $polo): void {
                $dt->merchandise()->whereNotNull('provided_at')
                    ->each(static function (Merchandise $merch) use ($dt, $shirt, $polo): void {
                        // phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed
                        if ($merch->id === $shirt->id) {
                            $dt->swag_shirt_provided = $merch->pivot->provided_at;
                            $dt->swag_shirt_providedBy = $merch->pivot->provided_by;
                            $dt->save();
                        } elseif ($merch->id === $polo->id) {
                            $dt->swag_polo_provided = $merch->pivot->provided_at;
                            $dt->swag_polo_providedBy = $merch->pivot->provided_by;
                            $dt->save();
                        }
                        // phpcs:enable
                    });
            });
        });
    }
}
