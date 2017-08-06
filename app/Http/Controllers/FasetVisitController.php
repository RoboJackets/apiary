<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\FasetVisit;
use App\fasetResponse;

class FasetVisitController extends Controller
{
    public function visit(Request $request)
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
        $visit = FasetVisit::with(['fasetResponses'])->find($id);

        if ($visit) {
            $visit['status'] = "success";
            return response()->json($visit);
        } else {
            return response()->json(['status' => 'error', 'message' => 'visit_not_found'], 404);
        }
    }
    
    public function list(Request $request)
    {
        $visits = FasetVisit::all();
        return response()->json($visits);
    }
}
