<?php

declare(strict_types=1);

use App\Models\Travel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('travel', static function (Blueprint $table): void {
            $table->string('status');
            $table->unsignedInteger('created_by_user_id')->nullable();

            $table->foreign('created_by_user_id')->references('id')->on('users');
        });

        Travel::query()->withTrashed()->update([
            'status' => 'complete',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel', static function (Blueprint $table): void {
            $table->dropForeign('created_by_user_id');

            $table->dropColumn('created_by_user_id');
            $table->dropColumn('status');
        });
    }
};
