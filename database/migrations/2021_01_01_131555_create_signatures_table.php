<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSignaturesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('signatures', static function (Blueprint $table): void {
            $table->id();

            // relationships to other models
            $table->foreignId('membership_agreement_template_id')->constrained();

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            // paper signature metadata
            $table->unsignedInteger('uploaded_by')->nullable();
            $table->foreign('uploaded_by')->references('id')->on('users');
            $table->string('scanned_agreement')->nullable()->unique();
            $table->boolean('electronic');

            // electronic signature metadata
            $table->string('cas_host', 255)->nullable();
            $table->string('cas_service_url_hash', 255)->nullable()->unique();
            $table->string('cas_ticket', 255)->nullable()->unique();
            $table->ipAddress('ip_address')->nullable();
            $table->json('ip_address_location_estimate')->nullable();
            $table->longText('user_agent')->nullable();

            // whether this signature is complete
            $table->boolean('complete')->default(false);

            // timestamps
            $table->dateTimeTz('render_timestamp')->nullable();
            $table->dateTimeTz('redirect_to_cas_timestamp')->nullable();
            $table->dateTimeTz('cas_ticket_redeemed_timestamp')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signatures');
    }
}
