<?php

declare(strict_types=1);

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
        Schema::create('resumes', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('filepath');
            $table->timestamp('last_uploaded_at')->nullable();
            $table->timestamps();
        });

        User::whereNotNull('resume_date')->each(static function (User $user): void {
            $user->resume()->create([
                'filepath' => 'resumes/'.$user->uid.'.pdf',
                'last_uploaded_at' => $user->resume_date ?? now(),
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
