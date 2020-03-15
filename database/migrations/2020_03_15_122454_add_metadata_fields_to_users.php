<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetadataFieldsToUsers extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::connection()->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping(
            'enum',
            'string'
        );

        Schema::table('users', static function (Blueprint $table) {
            $table->string('create_reason', 255)->default('cas_login');
            $table->boolean('has_ever_logged_in')->default(true);
            $table->boolean('is_service_account')->default(false);
            $table->string('primary_affiliation')->nullable();
            $table->timestamp('last_login')->nullable();
            $table->string('gtDirGUID')->nullable();
        });

        Schema::table('users', static function (Blueprint $table) {
            $table->string('create_reason', 255)->default(null)->change();
            $table->boolean('has_ever_logged_in')->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', static function (Blueprint $table) {
            $table->dropColumn('create_reason');
            $table->dropColumn('has_ever_logged_in');
            $table->dropColumn('is_service_account');
            $table->dropColumn('primary_affiliation');
            $table->dropColumn('last_login');
        });
    }
}
