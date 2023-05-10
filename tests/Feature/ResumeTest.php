<?php

declare(strict_types=1);

// @phan-file-suppress PhanPossiblyFalseTypeArgument

namespace Tests\Feature;

use App\Models\DuesPackage;
use App\Models\DuesTransaction;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\UsersSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

final class ResumeTest extends TestCase
{
    /**
     * Test the validation of resume book uploads.
     */
    public function testResumeUpload(): void
    {
        $user = $this->getTestUser(['member']);

        $this->seed(UsersSeeder::class);
        $alternateUser = User::where('id', '!=', $user->id)->first();
        $alternateId = $alternateUser->id;

        // Underscore ensures the UsersSeeder can't create that username even with a lot of luck
        $fakeUserId = '_nonexistentuser';

        Storage::fake('local');

        // Check they started with no resume uploaded
        $response = $this->actingAs($user, 'api')->get('/users/'.$user->id.'/resume');
        $response->assertStatus(404);
        $response->assertJson(static function (AssertableJson $json): void {
            $json->where('status', 'error')
                ->where('message', 'The requested user has no resume.');
        });

        // Check they started with no resume uploaded when searching by uid instead of id
        $response = $this->actingAs($user, 'api')->get('/users/'.$user->uid.'/resume');
        $response->assertStatus(404);
        $response->assertJson(static function (AssertableJson $json): void {
            $json->where('status', 'error')
                ->where('message', 'The requested user has no resume.');
        });

        $textFile = UploadedFile::fake()->create('resume.txt', 100);
        $bigTextFile = UploadedFile::fake()->create('resume.txt', 1000100);
        $docxFile = UploadedFile::fake()
            ->createWithContent('resume.docx', file_get_contents(resource_path('test/resume.docx')));
        $image = UploadedFile::fake()->image('resume.png');
        $onePagePdfContents = file_get_contents(resource_path('test/resume.pdf'));
        $pdfOnePage = UploadedFile::fake()
            ->createWithContent('resume.docx', $onePagePdfContents);
        $pdfTwoPage = UploadedFile::fake()
            ->createWithContent('resume.docx', file_get_contents(resource_path('test/resume-twopage.pdf')));

        // Check an upload for a nonexistent user fails
        $response = $this->actingAs($user, 'api')->post('/api/v1/users/'.$fakeUserId.'/resume', [
            'resume' => $pdfOnePage,
        ]);
        $response->assertStatus(422);
        Storage::disk('local')->assertMissing('resumes/'.$fakeUserId.'.pdf');

        // Check an upload for another user fails
        $response = $this->actingAs($user, 'api')->post('/api/v1/users/'.$alternateId.'/resume', [
            'resume' => $pdfOnePage,
        ]);
        $response->assertStatus(403);
        Storage::disk('local')->assertMissing('resumes/'.$alternateId.'.pdf');

        // Check an upload with no dues transaction fails
        $response = $this->actingAs($user, 'api')->post('/api/v1/users/'.$user->id.'/resume', [
            'resume' => $pdfOnePage,
        ]);
        $response->assertStatus(403);
        Storage::disk('local')->assertMissing('resumes/'.$user->uid.'.pdf');
        $response->assertJson(static function (AssertableJson $json): void {
            $json->where('status', 'error')
                ->where('message', 'inactive');
        });

        // Create dues package / transaction / payment
        $package = DuesPackage::factory()->make();
        $package->effective_end = now()->addMonths(10);
        $package->restricted_to_students = true;
        $package->save();
        $transaction = new DuesTransaction();
        $transaction->user_id = $user->id;
        $transaction->dues_package_id = $package->id;
        $transaction->save();
        $payment = new Payment();
        $payment->payable_id = $transaction->id;
        $payment->payable_type = DuesTransaction::getMorphClassStatic();
        $payment->method = 'square';
        $payment->amount = $package->cost;
        // @phan-suppress-next-line PhanTypeMismatchPropertyProbablyReal
        $payment->processing_fee = 0;
        $payment->save();

        // Ideally this would check that uploading no file fails nicely, but there's no easy way to fake that.

        // Check an upload of a PNG fails
        $response = $this->actingAs($user, 'api')->post('/api/v1/users/'.$user->id.'/resume', [
            'resume' => $image,
        ]);
        $response->assertStatus(400);
        $response->assertJson(static function (AssertableJson $json): void {
            $json->where('status', 'error')
                ->where('message', 'resume_not_pdf');
        });
        Storage::disk('local')->assertMissing('resumes/'.$user->uid.'.pdf');

        // Check an upload of a TXT fails
        $response = $this->actingAs($user, 'api')->post('/api/v1/users/'.$user->id.'/resume', [
            'resume' => $textFile,
        ]);
        $response->assertStatus(400);
        $response->assertJson(static function (AssertableJson $json): void {
            $json->where('status', 'error')
                ->where('message', 'resume_not_pdf');
        });
        Storage::disk('local')->assertMissing('resumes/'.$user->uid.'.pdf');

        // Check an upload of a DOCX fails
        $response = $this->actingAs($user, 'api')->post('/api/v1/users/'.$user->id.'/resume', [
            'resume' => $docxFile,
        ]);
        $response->assertStatus(400);
        $response->assertJson(static function (AssertableJson $json): void {
            $json->where('status', 'error')
                ->where('message', 'resume_not_pdf');
        });
        Storage::disk('local')->assertMissing('resumes/'.$user->uid.'.pdf');

        // Check an upload of a multi-page PDF fails
        $response = $this->actingAs($user, 'api')->post('/api/v1/users/'.$user->id.'/resume', [
            'resume' => $pdfTwoPage,
        ]);
        $response->assertStatus(400);
        $response->assertJson(static function (AssertableJson $json): void {
            $json->where('status', 'error')
                ->where('message', 'resume_not_one_page');
        });
        Storage::disk('local')->assertMissing('resumes/'.$user->uid.'.pdf');

        // Check file size limit
        $response = $this->actingAs($user, 'api')->post('/api/v1/users/'.$user->id.'/resume', [
            'resume' => $bigTextFile,
        ]);
        $response->assertStatus(400);
        $response->assertJson(static function (AssertableJson $json): void {
            $json->where('status', 'error')
                ->where('message', 'too_big');
        });
        Storage::disk('local')->assertMissing('resumes/'.$user->uid.'.pdf');

        $package->restricted_to_students = false;
        $package->save();

        // Check student status is checked
        $response = $this->actingAs($user, 'api')->post('/api/v1/users/'.$user->id.'/resume', [
            'resume' => $pdfOnePage,
        ]);
        $response->assertStatus(403);
        $response->assertJson(static function (AssertableJson $json): void {
            $json->where('status', 'error')
                ->where('message', 'ineligible');
        });
        Storage::disk('local')->assertMissing('resumes/'.$user->uid.'.pdf');

        $package->restricted_to_students = true;
        $package->save();

        // Check an upload of a single page PDF passes
        $response = $this->actingAs($user, 'api')->post('/api/v1/users/'.$user->id.'/resume', [
            'resume' => $pdfOnePage,
        ]);
        $response->assertStatus(200);
        $response->assertJson(static function (AssertableJson $json): void {
            $json->where('status', 'success');
        });
        Storage::disk('local')->assertExists('resumes/'.$user->uid.'.pdf');

        // Check that the timestamp was set
        $user->refresh();
        // Give a +/-5sec tolerance on the comparison
        $this->assertEqualsWithDelta($user->resume_date->timestamp, now()->timestamp, 5);

        // Check that the resume can be downloaded
        $response = $this->actingAs($user, 'api')->get('/users/'.$user->id.'/resume');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Length', strlen($onePagePdfContents));

        // Check that another user's resume cannot be downloaded
        $response = $this->actingAs($alternateUser, 'api')->get('/users/'.$user->id.'/resume');
        $response->assertStatus(403);

        // Check that a resume cannot be uploaded for another user
        $response = $this->actingAs($user, 'api')->post('/api/v1/users/'.$alternateId.'/resume', [
            'resume' => $pdfOnePage,
        ]);
        $response->assertStatus(403);
        $response->assertJson(static function (AssertableJson $json): void {
            $json->where('status', 'error')
                ->where('message', 'You do not have permission to upload that resume.');
        });
        Storage::disk('local')->assertMissing('resumes/'.$alternateId.'.pdf');

        // Check that nothing crazy happened with storing wrong extensions
        Storage::disk('local')->assertMissing('resumes/'.$user->uid.'.txt');
        Storage::disk('local')->assertMissing('resumes/'.$user->uid.'.png');
        Storage::disk('local')->assertMissing('resumes/'.$user->uid.'.docx');
    }

    public function testNonexistentResumeDownload(): void
    {
        $user = $this->getTestUser(['member']);
        $response = $this->actingAs($user, 'web')->get('/users/_nonexistentuser/resume');
        $response->assertStatus(422);

        $user = $this->getTestUser(['admin']);
        $response = $this->actingAs($user, 'web')->get('/users/_nonexistentuser/resume');
        $response->assertStatus(422);
    }
}
