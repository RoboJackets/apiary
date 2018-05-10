<?php

use Illuminate\Database\Migrations\Migration;

class Base64UrlEncode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('faset_visits')
            ->update(['visit_token' => DB::raw("REPLACE(visit_token, '+', '_')")]);
        DB::table('faset_visits')
            ->update(['visit_token' => DB::raw("REPLACE(visit_token, '/', '-')")]);
        DB::table('faset_visits')
            ->update(['visit_token' => DB::raw("REPLACE(visit_token, '=', '.')")]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('faset_visits')
            ->update(['visit_token' => DB::raw("REPLACE(visit_token, '_', '+')")]);
        DB::table('faset_visits')
            ->update(['visit_token' => DB::raw("REPLACE(visit_token, '-', '/')")]);
        DB::table('faset_visits')
            ->update(['visit_token' => DB::raw("REPLACE(visit_token, '.', '=')")]);
    }
}
