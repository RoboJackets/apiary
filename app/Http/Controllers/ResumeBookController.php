<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ResumeSearchRequest;
use App\Models\User;
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
            return response()
                ->file(Storage::disk('local')
                    ->path('resumes/'.$uid.'.pdf'), ['Content-Type' => 'application/pdf']);
        } catch (FileNotFoundException) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'The requested user has no resume.',
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

        $users = $this->filterUsers($majors, $graduation_semesters);

        return response()->json([
            'status' => 'success',
            'users' => $users,
        ]);
    }

    /**
     * Gets all graduation semesters for RoboJackets members.
     */
    public function getGraduationSemesters()
    {
        $semesters = User::select('graduation_semester')
            ->distinct()
            ->orderByDesc('graduation_semester')
            ->pluck('graduation_semester')
            ->mapWithKeys(static fn ($code): array => [$code => self::formatGradSemester($code)]);

        return response()->json([
            'status' => 'success',
            'graduation_semesters' => $semesters,
        ]);
    }

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

    private function filterUsers(array $majors, array $graduation_semesters): array
    {
        $usernames = collect(Storage::disk('local')->files('resumes'))
            ->map(static fn ($path) => pathinfo($path, PATHINFO_FILENAME))
            ->toArray();
        $users = User::active()->whereIn('uid', $usernames);

        if (! ($majors === [])) {
            $users = $users->whereHas('majors', static fn ($query) => $query->whereIn('majors.id', $majors));
        }

        if (! ($graduation_semesters === [])) {
            $users = $users->whereIn('graduation_semester', $graduation_semesters);
        }

        $users = $users->with('majors')
            ->get(['id', 'uid', 'first_name', 'last_name', 'graduation_semester', 'gt_email'])
            ->map(fn ($user) => array_merge($user->toArray(), [
                'full_name' => $user->first_name.' '.$user->last_name,
                'majors' => $user->majors->map(static fn ($major): array => [
                    'id' => $major->id,
                    'name' => $major->display_name ?? $major->gtad_majorgroup_name,
                ])->toArray(),
                'graduation_semester' => $this->formatGradSemester($user->graduation_semester),
            ]))
            ->toArray();

        return $users;
    }
}
