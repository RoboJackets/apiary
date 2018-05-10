<?php

use Illuminate\Database\Migrations\Migration;

class UpdateUserNameConcat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function () {
            DB::statement("ALTER TABLE `users` CHANGE COLUMN `name` `name` VARCHAR(255) GENERATED ALWAYS AS (concat_ws(' ',`first_name`,`last_name`)) VIRTUAL;");
            DB::statement("ALTER TABLE `users` CHANGE COLUMN `full_name` `full_name` VARCHAR(255) GENERATED ALWAYS AS (concat_ws(' ',`first_name`,`middle_name`,`last_name`)) VIRTUAL");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::transaction(function () {
            DB::statement("ALTER TABLE `users` CHANGE COLUMN `name` `name` VARCHAR(255) GENERATED ALWAYS AS (CONCAT(first_name,' ',last_name)) VIRTUAL;");
            DB::statement("ALTER TABLE `users` CHANGE COLUMN `full_name` `full_name` VARCHAR(255) GENERATED ALWAYS AS (CONCAT(first_name,' ',middle_name,' ',last_name)) VIRTUAL");
        });
    }
}
