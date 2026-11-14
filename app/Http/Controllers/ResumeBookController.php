<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ResumeSearchRequest;
use App\Models\Major;
use App\Models\Resume;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class ResumeBookController
{
    /**
     * Displays resume book index page, which serves as the home page of the resume book.
     */
    public function index()
    {
        return view('sponsors.home');
    }

    /**
     * Shows a resume given a user ID.
     * Will later be adapted to use Resume model when developed.
     *
     * @param  $uid  string representing username for user we are getting the resume from.
     */
    public function show(string $uid)
    {
        try {
            $user = User::where('uid', $uid)->with('resume')->firstOrFail();

            return response()
                ->file($user->resume->absolute_file_path, ['Content-Type' => 'application/pdf']);
        } catch (FileNotFoundException) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'The requested user has no resume.',
                ],
                404
            );
        } catch (ModelNotFoundException) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'The requested user does not exist.',
                ],
                404
            );
        }
    }

    /**
     * Searches for resumes matching a query defined in a POST request.
     *
     * @param  $request  Request object containing arrays of parameters to search.
     */
    public function search(ResumeSearchRequest $request)
    {
        $validated = $request->validated();

        $majors = $validated['majors'] ?? [];
        $graduation_semesters = $validated['graduation_semesters'] ?? [];
        $search_terms = $validated['search_terms'] ?? '';

        $users = $this->filterUsers($majors, $graduation_semesters, $search_terms);

        return response()->json([
            'status' => 'success',
            'users' => $users->map(static fn (User $user): array => array_merge($user->toArray(), [
                'graduation_semester' => self::formatGradSemester($user->graduation_semester),
            ])),
        ]);
    }

    /**
     * Gets all graduation semesters for RoboJackets members.
     */
    public function getGraduationSemesters()
    {
        $semesters = User::with('resume')
            ->select('graduation_semester')
            ->whereHas('resume', static function (Builder $q): void {
                $q->visible();
            })
            ->distinct()
            ->orderByDesc('graduation_semester')
            ->pluck('graduation_semester')
            ->filter(static fn (?string $code): bool => $code !== null &&
                $code !== '' &&
                strlen($code) === 6 &&
                in_array(substr($code, 4, 2), ['02', '05', '08'], true))
            ->mapWithKeys(static fn (?string $code): array => [$code => self::formatGradSemester($code)]);

        return response()->json([
            'status' => 'success',
            'graduation_semesters' => $semesters,
        ]);
    }

    public function getMajors()
    {
        $majors = Major::getResumeMajors();

        return response()->json([
            'status' => 'success',
            'majors' => $majors,
        ]);
    }

    /**
     * Exports resumes matching the current search terms and filters.
     * Exports name, email, major, and graduation term.
     *
     * @param  $request  Request object containing arrays of parameters to search.
     */
    public function export(ResumeSearchRequest $request)
    {
        $validated = $request->validated();

        $majors = $validated['majors'] ?? [];
        $graduation_semesters = $validated['graduation_semesters'] ?? [];
        $search_terms = $validated['search_terms'] ?? '';

        $users = $this->filterUsers($majors, $graduation_semesters, $search_terms);

        return response()->streamDownload(static function () use ($users): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Email', 'Major', 'Graduation Term']);
            foreach ($users as $user) {
                fputcsv($out, [
                    $user->full_name,
                    $user->gt_email,
                    $user->majors
                        ->map(static fn (Major $major): ?string => $major->display_name ?? $major->gtad_majorgroup_name)
                        ->implode(', '),
                    self::formatGradSemester($user->graduation_semester)['name'],
                ]);
            }
            fclose($out);
        }, 'robojackets-resume-book.csv', ['Content-Type' => 'text/csv']);
    }

    /** @psalm-pure */
    private static function formatGradSemester(?string $code): array
    {
        if ($code === null || $code === '') {
            return ['code' => null, 'name' => 'Unspecified'];
        }
        if (strlen($code) !== 6) {
            return ['code' => $code, 'name' => $code];
        }

        $months = [
            '02' => 'Spring',
            '05' => 'Summer',
            '08' => 'Fall',
        ];

        $year = substr($code, 0, 4);
        $month = substr($code, 4, 2);

        return ['code' => $code, 'name' => ($months[$month] ?? $month).' '.$year];
    }

    /**
     * Filters users by searching their indexed resume documents and returns users with their resumes.
     *
     * @param  array<string>  $majors  Major display names to include.
     * @param  array<string>  $graduation_semesters  Graduation semester codes to include.
     * @param  string  $search_terms  Search terms to match against resume text.
     */
    private function filterUsers(
        array $majors,
        array $graduation_semesters,
        string $search_terms
    ): \Illuminate\Database\Eloquent\Collection {
        $usernames = collect(Storage::disk(Resume::storageDisk())->files(Resume::storageDirectory()))
            ->map(static fn (string $path): string => pathinfo($path, PATHINFO_FILENAME))
            ->toArray();
        $candidate_ids = User::active()->whereIn('uid', $usernames)->pluck('id')->all();

        if ($candidate_ids === []) {
            return (new User())->newCollection();
        }

        $resume_search = Resume::search($search_terms)
            ->whereIn('user_id', $candidate_ids)
            ->take(count($candidate_ids));

        if ($graduation_semesters !== []) {
            $resume_search->whereIn('graduation_semester', $graduation_semesters);
        }

        if ($majors !== []) {
            $resume_search->whereIn('major', $majors);
        }

        $matching_user_ids = $resume_search->get()->pluck('user_id')->unique()->all();

        if ($matching_user_ids === []) {
            return (new User())->newCollection();
        }

        return User::active()
            ->whereIn('id', $matching_user_ids)
            ->select(['id', 'uid', 'first_name', 'last_name', 'gt_email', 'graduation_semester'])
            ->with(['resume', 'majors:id,display_name,gtad_majorgroup_name'])
            ->get();
    }
}
