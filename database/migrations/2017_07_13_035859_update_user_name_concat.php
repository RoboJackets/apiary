<?php

declare(strict_types=1);

// phpcs:disable Generic.Strings.UnnecessaryStringConcat.Found

use Illuminate\Database\Migrations\Migration;

class UpdateUserNameConcat extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(static function (): void {
            DB::statement('ALTER TABLE `users` CHANGE COLUMN `name` `name` VARCHAR(255) GENERATED ALWAYS AS (concat_ws'
                ."(' ',`first_name`,`last_name`)) VIRTUAL;");
            DB::statement('ALTER TABLE `users` CHANGE COLUMN `full_name` `full_name` VARCHAR(255) GENERATED ALWAYS AS '
                ."(concat_ws(' ',`first_name`,`middle_name`,`last_name`)) VIRTUAL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::transaction(static function (): void {
            DB::statement('ALTER TABLE `users` CHANGE COLUMN `name` `name` VARCHAR(255) GENERATED ALWAYS AS (CONCAT('
                ."first_name,' ',last_name)) VIRTUAL;");
            DB::statement('ALTER TABLE `users` CHANGE COLUMN `full_name` `full_name` VARCHAR(255) GENERATED ALWAYS AS '
                ."(CONCAT(first_name,' ',middle_name,' ',last_name)) VIRTUAL");
        });
    }
}
