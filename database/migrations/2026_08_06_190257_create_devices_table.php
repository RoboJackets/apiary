<?php

declare(strict_types=1);

use App\Models\User;
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
        Schema::create('devices', static function (Blueprint $table): void {
            $table->unsignedInteger('serial_number')->primary();
            $table->tinyText('manufacturer');
            $table->tinyText('model');
            $table->tinyText('hardware_version');
            $table->tinyText('bluetooth_firmware_version');
            $table->tinyText('bluetooth_software_version');
            $table->tinyText('bootloader_version');
            $table->tinyText('application_version');
            $table->unsignedTinyInteger('battery_percentage');
            $table->foreignIdFor(User::class, 'last_seen_user_id');
            $table->timestamp('last_seen_at');
            $table->ipAddress('last_seen_ip_address');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
