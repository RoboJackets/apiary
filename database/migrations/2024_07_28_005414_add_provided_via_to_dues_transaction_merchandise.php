<?php

declare(strict_types=1);

use App\Models\DuesTransactionMerchandise;
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
        Schema::table('dues_transaction_merchandise', static function (Blueprint $table): void {
            $table->string('provided_via')->after('provided_by')->nullable();
        });

        DuesTransactionMerchandise::whereNotNull('provided_at')
            ->whereNull('provided_by')
            ->whereNull('provided_via')
            ->update(['provided_via' => 'Historical dues import']);

        DuesTransactionMerchandise::whereNotNull('provided_at')
            ->whereNotNull('provided_by')
            ->whereColumn('provided_at', '=', 'updated_at')
            ->whereNull('provided_via')
            ->update(['provided_via' => 'Nova']);

        DuesTransactionMerchandise::whereNotNull('provided_at')
            ->whereNotNull('provided_by')
            ->whereColumn('provided_at', '!=', 'updated_at')
            ->whereNull('provided_via')
            ->update(['provided_via' => 'Legacy web admin']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dues_transaction_merchandise', static function (Blueprint $table): void {
            $table->dropColumn('provided_via');
        });
    }
};
