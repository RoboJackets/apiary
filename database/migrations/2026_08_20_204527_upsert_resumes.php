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
        Schema::table('resumes', static function (Blueprint $table) {
            $table->unsignedInteger('user_id')->change();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->unique('user_id');
        });

        DB::table('users')->select(['id', 'resume_date'])
            ->whereNotNull('resume_date')
            ->orderBy('id')
            ->chunk(200, static function ($rows) {
                $data = $rows->map(static fn ($row) => [
                    'user_id' => $row->id,
                    'updated_at' => $row->resume_date,
                    'created_at' => now(),
                ])->toArray();

                DB::table('resumes')->insertOrIgnore($data);
            });

        Schema::table('users', static function (Blueprint $table) {
            $table->dropColumn('resume_date');
        });
    }

    /**
     * Reverse the migrations.
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

        DB::table('resumes')->truncate();

        Schema::table('resumes', static function (Blueprint $table) {
            $table->dropUnique(['user_id']);
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->change();
        });
    }
};
