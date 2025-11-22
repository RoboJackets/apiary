<?php

declare(strict_types=1);

// phpcs:disable Squiz.WhiteSpace.OperatorSpacing.SpacingBefore

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Passport;

return new class extends Migration
{
    /**
     * The database schema.
     *
     * @var \Illuminate\Database\Schema\Builder
     */
    protected $schema;

    /**
     * Create a new migration instance.
     */
    public function __construct()
    {
        $this->schema = Schema::connection($this->getConnection());
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('oauth_clients', function (Blueprint $table): void {
            if (! $this->schema->hasColumns('oauth_clients', ['owner_type', 'owner_id'])) {
                $table->nullableMorphs('owner', after: 'user_id');
            }

            $table->after('provider', function (Blueprint $table): void {
                if (! $this->schema->hasColumn('oauth_clients', 'redirect_uris')) {
                    $table->text('redirect_uris')->nullable();
                }

                if (! $this->schema->hasColumn('oauth_clients', 'grant_types')) {
                    $table->text('grant_types')->nullable();
                }
            });
        });

        foreach (Passport::client()->cursor() as $client) {
            Model::withoutTimestamps(static fn () => $client->forceFill([
                'owner_id' => $client->user_id,
                'owner_type' => $client->user_id
                    ? config('auth.providers.'.($client->provider ?: config('auth.guards.api.provider')).'.model')
                    : null,
                'redirect_uris' => $client->redirect_uris,
                'grant_types' => $client->grant_types,
            ])->save());
        }

        $this->schema->table('oauth_clients', static function (Blueprint $table): void {
            $table->dropIndex('oauth_clients_user_id_index');

            $table->dropColumn(['user_id', 'redirect', 'personal_access_client', 'password_client']);

            $table->text('redirect_uris')->nullable(false)->change();
            $table->text('grant_types')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // deliberately not supporting this due to complexity
    }

    /**
     * Get the migration connection name.
     */
    #[\Override]
    public function getConnection(): string
    {
        return config('passport.storage.database.connection');
    }
};
