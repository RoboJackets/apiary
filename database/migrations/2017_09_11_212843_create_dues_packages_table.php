<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDuesPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dues_packages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->boolean('eligible_for_shirt')->default(false);
            $table->boolean('eligible_for_polo')->default(false);
            $table->timestamp('effective_start')->useCurrent();
            $table->timestamp('effective_end')->useCurrent();
            $table->decimal('cost');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('dues_packages');
    }
}
