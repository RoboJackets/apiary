<?php

use Illuminate\Database\Migrations\Migration;

class EncryptUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $plugin = DB::select("SELECT PLUGIN_NAME, PLUGIN_STATUS FROM INFORMATION_SCHEMA.PLUGINS
          WHERE PLUGIN_NAME LIKE 'keyring%'");
        if (1 === count($plugin)) {
            DB::raw("ALTER TABLE 'users' ENCRYPTION='Y'");
        } else {
            die('No keyring plugin found.');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::raw("ALTER TABLE 'users' ENCRYPTION='N'");
    }
}
