<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemoteAttendancelinksTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('remote_attendance_links', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('secret')->unique();
            // useCurrent() for this because the migration required a default value.
            $table->timestamp('expires_at')->useCurrent();

            $table->string('redirect_url', 1023)->nullable();
            $table->string('note')->nullable();

            $table->string('attendable_type');
            $table->unsignedInteger('attendable_id');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remote_attendance_links');
    }
}
