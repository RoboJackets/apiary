<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class ResumeBookController extends Controller
{
    /**
     * Displays resume book index page, which serves as the home page of the resume book.
     */
    public function index()
    {
        // Should be replaced with DB calls when Resume model created.
        /*$files = Storage::disk('local')->files('resumes');
        $ids = [];
        foreach ($files as $file) {
            $filename = basename($file);
            $id = pathinfo($filename, PATHINFO_FILENAME);
            if ($id !== '') {
                $ids[] = $id;
            }
        }
        $ids = array_values(array_unique($ids));

        $users = User::whereIn('uid', $ids)->get(['id', 'name', 'major', 'graduation_semester'])->toArray();*/

        return view('sponsors.resume-book', ['users' => [] /*$users*/]);
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
