<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
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
        // Should be replaced with DB calls when Resume model created.
        $usernames = collect(Storage::disk('local')->files('resumes'))
            ->map(fn ($path) => pathinfo($path, PATHINFO_FILENAME))
            ->toArray();

        $users = User::whereIn('uid', $usernames)
            ->with('majors')
            ->get()
            ->map(fn ($user) => array_merge($user->toArray(), [
                'majors' => $user->majors->map(fn ($major) => [
                    'id'   => $major->id,
                    'name' => $major->display_name ?? $major->gtad_majorgroup_name,
                ])->toArray(),
                'graduation_semester' => $this->formatGradSemester($user->graduation_semester),
            ]))
            ->toArray();

        return view('sponsors.home', ['users' => $users]);
    } // Currently producing unterminated string error. TODO: Find cause

    /**
     * Shows a resume given a user ID.
     * Will later be adapted to use Resume model when developed.
     *
     * @param $uid string representing username for user we are getting the resume from.
     */
    public function show(string $uid)
    {
        try {
            return response()->file(Storage::disk('local')->path('resumes/'.$id.'.pdf'));
        } catch (FileNotFoundException $e) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'The requested user has no resume.',
                ],
                404
            );
        }

    }

    public function search(Request $request)
    {
    }

    private function formatGradSemester(?string $code): ?string
    {
        if (!$code || strlen($code) !== 6) return $code;

        $months = [
            '01' => 'January',  '02' => 'February', '03' => 'March',
            '04' => 'April',    '05' => 'May',       '06' => 'June',
            '07' => 'July',     '08' => 'August',    '09' => 'September',
            '10' => 'October',  '11' => 'November',  '12' => 'December',
        ];

        $year  = substr($code, 0, 4);
        $month = substr($code, 4, 2);

        return ($months[$month] ?? $month) . ' ' . $year;
    }
}
