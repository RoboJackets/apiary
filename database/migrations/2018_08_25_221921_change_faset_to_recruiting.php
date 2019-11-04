<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

class ChangeFasetToRecruiting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Rename columns
        Schema::table('faset_visits', function (Blueprint $table) {
            $table->renameColumn('faset_name', 'recruiting_name');
            $table->renameColumn('faset_email', 'recruiting_email');
        });

        Schema::table('faset_responses', function (Blueprint $table) {
            $table->dropForeign('faset_responses_faset_visit_id_foreign');
            $table->renameColumn('faset_visit_id', 'recruiting_visit_id');
        });

        $permissions = Permission::where('name', 'LIKE', '%-faset-%')->get();
        foreach ($permissions as $p) {
            $new_name = str_replace('faset', 'recruiting', $p->name);
            $p->name = $new_name;
            $p->save();
        }

        // Rename the tables
        Schema::rename('faset_visits', 'recruiting_visits');
        Schema::rename('faset_responses', 'recruiting_responses');

        // Put the foreign key constraint back
        Schema::table('recruiting_responses', function (Blueprint $table) {
            $table->foreign('recruiting_visit_id')->references('id')->on('recruiting_visits');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Rename columns
        Schema::table('recruiting_visits', function (Blueprint $table) {
            $table->renameColumn('recruiting_name', 'faset_name');
            $table->renameColumn('recruiting_email', 'faset_email');
        });

        Schema::table('recruiting_responses', function (Blueprint $table) {
            $table->dropForeign('recruiting_responses_recruiting_visit_id_foreign');
            $table->renameColumn('recruiting_visit_id', 'faset_visit_id');
        });

        $permissions = Permission::where('name', 'LIKE', '%-recruiting-%')->get();
        foreach ($permissions as $p) {
            $new_name = str_replace('recruiting', 'faset', $p->name);
            $p->name = $new_name;
            $p->save();
        }

        // Put the tables back first
        Schema::rename('recruiting_visits', 'faset_visits');
        Schema::rename('recruiting_responses', 'faset_responses');

        // Put the foreign key constraint back
        Schema::table('faset_responses', function (Blueprint $table) {
            $table->foreign('faset_visit_id')->references('id')->on('faset_visits');
        });
    }
}
