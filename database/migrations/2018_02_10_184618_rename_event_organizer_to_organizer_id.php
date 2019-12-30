<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameEventOrganizerToOrganizerId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', static function (Blueprint $table): void {
            $table->renameColumn('organizer', 'organizer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', static function (Blueprint $table): void {
            $table->renameColumn('organizer_id', 'organizer');
        });
    }
}
