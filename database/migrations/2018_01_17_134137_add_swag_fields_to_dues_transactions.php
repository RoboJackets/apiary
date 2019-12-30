<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSwagFieldsToDuesTransactions extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dues_transactions', static function (Blueprint $table): void {
            //Change name and make these timestamps
            $table->dropColumn('received_polo');
            $table->timestamp('swag_polo_provided')->nullable()->after('id');
            $table->dropColumn('received_shirt');
            $table->timestamp('swag_shirt_provided')->nullable()->after('id');

            //Add provided by
            $table->unsignedInteger('swag_polo_providedBy')->nullable()->after('swag_polo_provided');
            $table->unsignedInteger('swag_shirt_providedBy')->nullable()->after('swag_shirt_provided');
            $table->foreign('swag_polo_providedBy')->references('id')->on('users');
            $table->foreign('swag_shirt_providedBy')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dues_transactions', static function (Blueprint $table): void {
            //Change name back and make these booleans
            $table->dropColumn('swag_polo_provided');
            $table->boolean('received_polo')->default(false)->after('id');
            $table->dropColumn('swag_shirt_provided');
            $table->boolean('received_shirt')->default(false)->after('id');

            //Remove provided by
            $table->dropForeign(['swag_polo_providedBy']);
            $table->dropForeign(['swag_shirt_providedBy']);
            $table->dropColumn('swag_polo_providedBy');
            $table->dropColumn('swag_shirt_providedBy');
        });
    }
}
