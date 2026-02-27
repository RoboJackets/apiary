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

        $users = User::whereIn('uid', $usernames)->get(['id', 'first_name', 'major', 'graduation_semester'])->toArray();

        return view('sponsors.resume-book', ['users' => $users]);
    } // Currently producing unterminated string error. TODO: Find cause

    //Unfinished
    // TODO: fix response
    public function show($id)
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
}
