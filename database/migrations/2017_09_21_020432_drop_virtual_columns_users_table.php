<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropVirtualColumnsUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', static function (Blueprint $table): void {
            $table->dropColumn('name');
            $table->dropColumn('full_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', static function (Blueprint $table): void {
            $table->string('name')
                ->virtualAs("concat_ws(' ',`first_name`,`last_name`)")->nullable()->after('preferred_name');
            $table->string('full_name')
                ->virtualAs("concat_ws(' ',`first_name`,`middle_name`,`last_name`)")->after('preferred_name');
        });
    }
}
