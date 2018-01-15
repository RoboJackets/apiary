<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\FasetVisit;
use App\FasetResponse;

class FasetVisitController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:read-faset-visits', ['only' => ['index']]);
        $this->middleware('permission:create-faset-visits', ['only' => ['store']]);
        $this->middleware('permission:read-faset-visits|read-faset-visits-own', ['only' => ['show']]);
        $this->middleware('permission:update-faset-visits|update-faset-visits-own', ['only' => ['update']]);
        $this->middleware('permission:update-faset-visits', ['only' => ['dedup']]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'faset_email' => 'required|email|max:255',
            'faset_name' => 'required|max:255'
        ]);

        try {
            DB::beginTransaction();
            $personInfo = $request->only(['faset_email', 'faset_name']);
            $visit = FasetVisit::create($personInfo);
            
            $fasetResponses = $request->only('faset_responses')['faset_responses'];

            foreach ($fasetResponses as $question) {
                foreach ($question as $surveyID => $fasetResponse) {
                    foreach ($fasetResponse as $answer) {
                        $visit->addFasetResponse($surveyID, $answer);
                    }
                }
            }

            DB::commit();
            Log::info('New FASET Visit Logged:', ['email' => $visit->faset_email]);

            return response()->json(array("status" => "success"));
        } catch (Exception $e) {
            Bugsnag::notifyException($e);
            DB::rollback();
            Log::error('New FASET visit save failed', ['error' => $e->getMessage()]);
            return response()->json(array("status" => "error"))->setStatusCode(500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id FasetVisit ID Number
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $requestingUser = $request->user();
        $visit = FasetVisit::with(['fasetResponses'])->find($id);
        if (!$visit) {
            return response()->json(['status' => 'error', 'message' => 'visit_not_found'], 404);
        }

        $requestedUser = $visit->user;
        //Enforce users only viewing their own FasetVisit (read-faset-visits-own)
        if ($requestingUser->cant('read-faset-visits') && $requestingUser->id != $requestedUser->id) {
            return response()->json(['status' => 'error',
                'message' => 'Forbidden - You do not have permission to view this FasetVisit.'], 403);
        }

        return response()->json(['status' => 'success', 'visit' => $visit]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id FasetVisit Id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //Update only included fields
        $this->validate($request, [
            'faset_name' => 'max:127',
            'faset_email' => 'email|max:127'
        ]);

        $visit = FasetVisit::find($id);
        if (!$visit) {
            return response()->json(['status' => 'error', 'message' => 'visit_not_found'], 404);
        }

        $requestingUser = $request->user();
        $requestedUser = $visit->user;
        //Enforce users only updating themselves (update-users-own)
        if ($requestingUser->cant('update-faset-visits') && $requestingUser->id != $requestedUser->id) {
            return response()->json(['status' => 'error',
                'message' => 'Forbidden - You do not have permission to update this FasetVisit.'], 403);
        }

        $visit->update($request->all());

        $visit = FasetVisit::with(['fasetResponses'])->find($id);

        if ($visit) {
            return response()->json(['status' => 'success', 'visit' => $visit]);
        } else {
            return response()->json(['status' => 'error', 'message' => 'visit_not_found'], 404);
        }
    }

    public function index(Request $request)
    {
        $visits = FasetVisit::all();
        return response()->json(['status' => 'success', 'visits' => $visits]);
    }

    public function dedup()
    {
        $visits = FasetVisit::all();
        $emails = [];
        foreach ($visits as $visit) {
            echo "Processing Visit $visit->id<br/>\n";
            if (!in_array($visit->faset_email, $emails)) {
                $emails[] = $visit->faset_email;
            } else {
                echo "Deleting Visit $visit->id<br/>\n";
                $count = FasetResponse::where('faset_visit_id', $visit->id)->count();
                echo "Deleting $count Responses for Visit $visit->id<br/>\n";
                foreach ($visit->fasetResponses as $response) {
                    echo "Deleting Response $response->response<br/>\n";
                    $deletedRows = FasetResponse::where('faset_visit_id', $visit->id)->delete();
                }
                $visit->delete();
            }
        }
    }
}
