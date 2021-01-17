<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use App\Models\DuesTransaction;
use App\Models\FiscalYear;
use App\Models\Merch;

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

            $shirt = Merch::firstOrCreate([
                'name' => 'T-Shirt',
                'fiscal_year_id' => $fy->id,
            ]);
            $polo = Merch::firstOrCreate([
                'name' => 'Polo',
                'fiscal_year_id' => $fy->id,
            ]);

            $startingYear = $fy->ending_year - 1;
            $endingYear = $fy->ending_year;

            $fallPackageName = 'Fall '.$startingYear;
            $springPackageName = 'Spring '.$endingYear;
            $studentFullYearName = 'Full Year ('.$startingYear.'-'.$endingYear.')';

            $fallPackage = $fy->packages()->where('name', $fallPackageName)->first();
            $springPackage = $fy->packages()->where('name', $springPackageName)->first();
            $studentFullYearPackage = $fy->packages()->where('name', $studentFullYearName)->first();

            if (null !== $fallPackage) {
                $fallPackage->merch()->attach($shirt, ['group' => 'Fall']);
            }
            if (null !== $springPackage) {
                $springPackage->merch()->attach($polo, ['group' => 'Spring']);
            }
            if (null !== $studentFullYearPackage) {
                $studentFullYearPackage->merch()->attach($shirt, ['group' => 'Fall']);
                $studentFullYearPackage->merch()->attach($polo, ['group' => 'Spring']);
            }

            $fy->transactions->each(static function (DuesTransaction $dt) use ($shirt, $polo): void {
                // phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed
                if (null !== $dt->swag_shirt_provided) {
                    $dt->merch()->attach($shirt, [
                        'provided_at' => $dt->swag_shirt_provided,
                        'provided_by' => $dt->swag_shirt_providedBy,
                    ]);
                }

                if (null !== $dt->swag_polo_provided) {
                    $dt->merch()->attach($polo, [
                        'provided_at' => $dt->swag_polo_provided,
                        'provided_by' => $dt->swag_polo_providedBy,
                    ]);
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
            $shirt = Merch::firstOrCreate([
                'name' => 'T-Shirt',
                'fiscal_year_id' => $fy->id,
            ]);
            $polo = Merch::firstOrCreate([
                'name' => 'Polo',
                'fiscal_year_id' => $fy->id,
            ]);

            DuesTransaction::get()->each(static function (DuesTransaction $dt) use ($shirt, $polo): void {
                $dt->merch()->whereNotNull('provided_at')
                    ->each(static function (Merch $merch) use ($dt, $shirt, $polo): void {
                        // phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed
                        if ($merch->id === $shirt->id) {
                            $dt->swag_shirt_provided = $merch->pivot->provided_at;
                            $dt->swag_shirt_providedBy = $merch->pivot->provided_by;
                        } elseif ($merch->id === $polo->id) {
                            $dt->swag_polo_provided = $merch->pivot->provided_at;
                            $dt->swag_polo_providedBy = $merch->pivot->provided_by;
                        }
                        // phpcs:enable
                    });
            });
        });
    }
}
