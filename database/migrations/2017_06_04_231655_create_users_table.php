<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('uid', 127)->unique();
            $table->unsignedInteger('gtid')->unique();
            $table->string('slack_id', 21)->nullable();
            $table->string('gt_email');
            $table->string('personal_email')->nullable();
            $table->string('first_name', 127);
            $table->string('middle_name', 127)->nullable();
            $table->string('last_name', 127);
            $table->string('preferred_name', 127)->nullable();
            $table->string('name')->virtualAs("CONCAT(first_name,' ',last_name)")->nullable();
            $table->string('full_name')->virtualAs("CONCAT(first_name,' ',middle_name,' ',last_name)");
            $table->string('phone', 15)->nullable();
            // Emergency Contact Info
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 15)->nullable();
            // Membership Info
            $table->char('join_semester', 6)->nullable();
            $table->char('graduation_semester', 6)->nullable();
            // Swag info
            $table->enum('shirt_size', ['s', 'm', 'l', 'xl', 'xxl', 'xxxl'])->nullable();
            $table->enum('polo_size', ['s', 'm', 'l', 'xl', 'xxl', 'xxxl'])->nullable();
            // Timestamps
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}
