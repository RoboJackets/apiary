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
        Schema::create('docusign_envelopes', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('signed_by');
            $table->string('url')->nullable();
            $table->string('envelope_id')->nullable();
            $table->morphs('signable');
            $table->boolean('complete');
            $table->string('membership_agreement_filename')->nullable();
            $table->string('travel_authority_filename')->nullable();
            $table->string('direct_bill_airfare_filename')->nullable();
            $table->string('covid_risk_filename')->nullable();
            $table->string('summary_filename')->nullable();
            $table->string('signer_ip_address')->nullable();
            $table->dateTimeTz('sent_at')->nullable();
            $table->dateTimeTz('viewed_at')->nullable();
            $table->dateTimeTz('signed_at')->nullable();
            $table->dateTimeTz('completed_at')->nullable();
            $table->softDeletesTz();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('docusign_envelopes');
    }
};
