<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        Schema::table('resumes', static function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('user')
                ->onDelete('cascade');

            $table->unique('user_id');
        });

        DB::table('users')->select('id', 'resume_date')
            ->orderBy('id')
            ->chunk(200, static function ($rows) {
                $data = $rows->map(static function ($row) {
                    return [
                        'user_id' => $row->id,
                        'updated_at' => $row->resume_date,
                        'created_at' => now(),
                    ];
                })->toArray();

                DB::table('resumes')->insertOrIgnore($data);
            });

        Schema::table('users', static function (Blueprint $table) {
            $table->dropColumn('resume_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @psalm-mutation-free
     */
    public function down(): void
    {
        Schema::table('users', static function (Blueprint $table) {
            $table->dateTime('resume_date')
                ->nullable();
        });

        DB::table('resumes')->orderBy('id')->chunk(200, static function ($rows) {
            foreach ($rows as $row) {
                DB::table('users')
                    ->where('id', $row->user_id)
                    ->update(['resume_date' => $row->updated_at]);
            }
        });

        Resume::truncate();
    }
};
