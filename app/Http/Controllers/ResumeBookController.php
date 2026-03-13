<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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
    public function search(Request $request)
    {
        $majors = $request->input('majors', []);
        $graduation_semesters = $request->input('graduation_semesters', []);

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
            ->pluck('graduation_semester')
            ->filter()
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
            '01' => 'January',  '02' => 'February', '03' => 'March',
            '04' => 'April',    '05' => 'May',       '06' => 'June',
            '07' => 'July',     '08' => 'August',    '09' => 'September',
            '10' => 'October',  '11' => 'November',  '12' => 'December',
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
            $users = $users->whereHas('majors', static function ($query) use ($majors): Builder {
                return $query->whereIn('majors.id', $majors);
            });
        }

        if (! ($graduation_semesters === [])) {
            $users = $users->whereIn('graduation_semester', $graduation_semesters);
        }

        $users = $users->with('majors')
            ->get()
            ->map(fn ($user) => array_merge($user->toArray(), [
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
