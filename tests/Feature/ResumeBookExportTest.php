<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Major;
use App\Models\Resume;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ResumeBookExportTest extends TestCase
{
    public function test_guest_cannot_export(): void
    {
        $response = $this->post(route('sponsor.export'), [
            'majors' => [],
            'graduation_semesters' => [],
        ]);

        $response->assertRedirect(route('sponsor.login'));
    }

    public function test_export_respects_filters(): void
    {
        Storage::fake('local');

        $sponsor = $this->getTestSponsor();
        $this->getTestSponsorDomain($sponsor);
        $sponsorUser = $this->getTestSponsorUser($sponsor);

        $cs = $this->createMajor('Computer Science', 'cs');
        $ee = $this->createMajor('Electrical Engineering', 'ee');

        $userOne = $this->createResumeBookUser(
            uid: 'exportuser1',
            firstName: 'User',
            lastName: 'One',
            graduationSemester: '202608',
            major: $cs,
        );
        $userTwo = $this->createResumeBookUser(
            uid: 'exportuser2',
            firstName: 'User',
            lastName: 'Two',
            graduationSemester: '202705',
            major: $ee,
        );

        $userOneRow = 'User One,'.$userOne->gt_email.',Computer Science,Fall 2026';
        $userTwoRow = 'User Two,'.$userTwo->gt_email.',Electrical Engineering,Summer 2027';

        $response = $this->actingAs($sponsorUser, 'sponsor')
            ->post(route('sponsor.export'), [
                'majors' => [],
                'graduation_semesters' => [],
            ]);

        $response->assertOk();
        $response->assertDownload('robojackets-resume-book.csv');

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Name,Email,Major,Graduation Term', $csv);
        $this->assertStringContainsString($userOneRow, $csv);
        $this->assertStringContainsString($userTwoRow, $csv);

        $response = $this->actingAs($sponsorUser, 'sponsor')
            ->post(route('sponsor.export'), [
                'majors' => ['Computer Science'],
                'graduation_semesters' => [],
            ]);

        $csv = $response->streamedContent();
        $this->assertStringContainsString($userOneRow, $csv);
        $this->assertStringNotContainsString($userTwo->gt_email, $csv);

        $response = $this->actingAs($sponsorUser, 'sponsor')
            ->post(route('sponsor.export'), [
                'majors' => [],
                'graduation_semesters' => [202608],
            ]);

        $csv = $response->streamedContent();
        $this->assertStringContainsString($userOneRow, $csv);
        $this->assertStringNotContainsString($userTwo->gt_email, $csv);

        $response = $this->actingAs($sponsorUser, 'sponsor')
            ->post(route('sponsor.export'), [
                'majors' => ['Mechanical Engineering'],
                'graduation_semesters' => [],
            ]);

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Name,Email,Major,Graduation Term', $csv);
        $this->assertStringNotContainsString($userOne->gt_email, $csv);
        $this->assertStringNotContainsString($userTwo->gt_email, $csv);
    }

    private function createMajor(string $displayName, string $ou): Major
    {
        $major = Major::where('display_name', $displayName)->first();
        if ($major !== null) {
            return $major;
        }

        $major = new Major();
        $major->display_name = $displayName;
        $major->gtad_majorgroup_name = $displayName;
        $major->whitepages_ou = $ou;
        $major->school = 'Test School';
        $major->save();

        return $major;
    }

    private function createResumeBookUser(
        string $uid,
        string $firstName,
        string $lastName,
        string $graduationSemester,
        Major $major,
    ): User {
        $user = User::factory()->create([
            'uid' => $uid,
            'gt_email' => $uid.'@gatech.edu',
            'first_name' => $firstName,
            'last_name' => $lastName,
            'primary_affiliation' => 'student',
            'is_service_account' => false,
            'create_reason' => 'phpunit',
            'has_ever_logged_in' => true,
            'graduation_semester' => $graduationSemester,
        ]);

        $user->majors()->syncWithoutDetaching([$major->id]);

        $package = self::createDuesPackage(null);
        $package->restricted_to_students = true;
        $package->save();
        self::createDuesTransactionForUser($package, $user, true);

        Storage::disk(Resume::storageDisk())->put(
            Resume::storagePathForUser($user),
            file_get_contents(resource_path('test/resume.pdf')) ?: 'pdf'
        );
        Resume::firstOrCreate(['user_id' => $user->id]);

        return $user->fresh();
    }
}
