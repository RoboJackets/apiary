<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

class Base64UrlEncode extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
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
     */
    public function down(): void
    {
        DB::table('faset_visits')
            ->update(['visit_token' => DB::raw("REPLACE(visit_token, '_', '+')")]);
        DB::table('faset_visits')
            ->update(['visit_token' => DB::raw("REPLACE(visit_token, '-', '/')")]);
        DB::table('faset_visits')
            ->update(['visit_token' => DB::raw("REPLACE(visit_token, '.', '=')")]);
    }
}
