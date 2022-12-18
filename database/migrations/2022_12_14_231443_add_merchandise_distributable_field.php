<?php

declare(strict_types=1);

use App\Models\Merchandise;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('merchandise', static function (Blueprint $table): void {
            $table->boolean('distributable')->default(false);
        });

        Merchandise::get()->each(static function (Merchandise $merchItem): void {
            $merchItem->distributable = ! Str::contains($merchItem->name, 'waive', true);
            $merchItem->save();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('merchandise', static function (Blueprint $table): void {
            $table->dropColumn('distributable');
        });
    }
};
