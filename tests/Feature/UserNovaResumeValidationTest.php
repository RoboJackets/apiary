<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

final class UserNovaResumeValidationTest extends TestCase
{
    /**
     * A User resource update should not resolve the detail-only resume HasOne
     * relationship, because Nova builds an empty Resume model for validation
     * attribute names and the Resume's File field resolves storage_path.
     */
    public function test_user_update_does_not_resolve_resume_hasone(): void
    {
        $admin = $this->getTestUser(['admin']);
        $user = User::factory()->create();

        $response = $this->actingAs($admin, 'web')
            ->putJson('/nova-api/users/'.$user->id, []);

        $response->assertStatus(422);
    }
}
