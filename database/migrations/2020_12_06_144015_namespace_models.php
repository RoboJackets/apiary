<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

class NamespaceModels extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('attendance')->where('attendable_type', 'App\Team')
            ->update(['attendable_type' => 'team']);
        DB::table('attendance')->where('attendable_type', 'App\Event')
            ->update(['attendable_type' => 'event']);

        DB::table('payments')
            ->update(['payable_type' => 'dues-transaction']);

        DB::table('model_has_roles')
            ->update(['model_type' => \App\Models\User::class]);
        DB::table('model_has_permissions')
            ->update(['model_type' => \App\Models\User::class]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('attendance')->where('attendable_type', 'team')
            ->update(['attendable_type' => 'App\Team']);
        DB::table('attendance')->where('attendable_type', 'event')
            ->update(['attendable_type' => 'App\Event']);

        DB::table('payments')
            ->update(['payable_type' => 'App\DuesTransaction']);

        DB::table('model_has_roles')
            ->update(['model_type' => 'App\User']);
        DB::table('model_has_permissions')
            ->update(['model_type' => 'App\User']);
    }
}
