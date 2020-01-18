<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClickUpFieldsToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', static function (Blueprint $table): void {
            $table->string('clickup_email', 255)->after('gmail_address')->nullable()->unique();
            $table->integer('clickup_id')->after('clickup_email')->nullable()->unique();
            $table->boolean('clickup_invite_pending')->after('clickup_id')->default(false);
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
            $table->dropColumn('clickup_email');
            $table->dropColumn('clickup_id');
            $table->dropColumn('clickup_invite_pending');
        });
    }
}
