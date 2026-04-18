<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('resumes', static function (Blueprint $table): void {
            $table->id();
            $table->string('user_id')->unique();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('filepath');
            $table->timestamp('last_uploaded_at')->nullable();
            $table->timestamps();
        });

        DB::table('users')
            ->whereNotNull('resume_date')
            ->orderBy('id')
            ->each(static function ($user) {
                DB::table('resumes')->insert([
                    'user_id' => $user->id,
                    'filepath' => 'resumes/'.$user->uid.'.pdf',
                    'last_uploaded_at' => $user->resume_date ?? now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resumes');
    }
};
