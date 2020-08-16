<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Exception;

class EncryptUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $plugin = DB::select("SELECT PLUGIN_NAME, PLUGIN_STATUS FROM INFORMATION_SCHEMA.PLUGINS
          WHERE PLUGIN_NAME LIKE 'keyring%'");
        if (1 !== count($plugin)) {
            throw new Exception('No keyring plugin found.');
        }

        DB::raw("ALTER TABLE 'users' ENCRYPTION='Y'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::raw("ALTER TABLE 'users' ENCRYPTION='N'");
    }
}
