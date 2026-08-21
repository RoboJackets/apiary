<?php

declare(strict_types=1);

use App\Models\Resume;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $resumes = Storage::disk('local')->files('resumes');
        $resumes = array_map(static fn ($f) => pathinfo($f, PATHINFO_FILENAME), $resumes);

        $users = User::whereIn('uid', $resumes)->get();
        Resume::upsert(
            $users->map(static fn ($user) => ['user_id' => $user->id])->all(),
            ['user_id'],
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Resume::truncate();
    }
};
