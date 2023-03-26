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
        Schema::create('travel', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('destination');
            $table->unsignedInteger('primary_contact_user_id');
            $table->date('departure_date');
            $table->date('return_date');
            $table->unsignedInteger('fee_amount');
            $table->longText('included_with_fee');
            $table->longText('not_included_with_fee')->nullable();
            $table->longText('documents_required')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('primary_contact_user_id')->references('id')->on('users');
            $table->unique('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel');
    }
};
