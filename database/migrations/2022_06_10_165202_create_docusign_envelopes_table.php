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
            $table->string('url');
            $table->string('envelope_id');
            $table->morphs('signable');
            $table->boolean('complete');
            $table->string('membership_agreement_filename');
            $table->string('travel_authority_filename');
            $table->string('direct_bill_airfare_filename');
            $table->string('covid_risk_filename');
            $table->string('summary_filename');
            $table->string('signer_ip_address');
            $table->dateTimeTz('sent_at');
            $table->dateTimeTz('viewed_at');
            $table->dateTimeTz('signed_at');
            $table->dateTimeTz('completed_at');
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
