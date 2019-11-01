<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoogleAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('google_accounts', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('email_address', 255)->unique();

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('google_account_user', static function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('google_account_id');
            $table->unsignedInteger('user_id');
            $table->timestamps();

            $table->foreign('google_account_id')->references('id')->on('google_accounts');
            $table->foreign('user_id')->references('id')->on('users');
        });

        $usersWithGmail = User::whereNotNull('gmail_address')->get();
        foreach ($usersWithGmail as $user) {
            $account = new GoogleAccount(['email_address' => $user->gmail_address]);
            $account->save();
            $user->googleAccounts()->attach($account);
        }

        Schema::table('users', static function (Blueprint $table): void {
            $table->dropColumn('gmail_address');
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
            $table->string('gmail_address', 255)->after('github_username')->nullable()->unique();
        });

        foreach (User::whereHas('googleAccount')->get() as $user) {
            $user->gmail_address = $user->googleAccounts()->first()->email_address;
            $user->save();
        }

        Schema::dropIfExists('google_account_user');
        Schema::dropIfExists('google_accounts');
    }
}
