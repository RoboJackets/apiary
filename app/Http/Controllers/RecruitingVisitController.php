<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireTernaryOperator

namespace App\Http\Controllers;

use Throwable;
use Validator;
use App\RecruitingVisit;
use App\RecruitingResponse;
use Illuminate\Http\Request;
use App\Traits\AuthorizeInclude;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\RecruitingVisit as RecruitingVisitResource;

class RecruitingVisitController extends Controller
{
    use AuthorizeInclude;

    public function __construct()
    {
        $this->middleware('permission:read-recruiting-visits', ['only' => ['index']]);
        $this->middleware('permission:create-recruiting-visits', ['only' => ['store']]);
        $this->middleware('permission:read-recruiting-visits|read-recruiting-visits-own', ['only' => ['show']]);
        $this->middleware('permission:update-recruiting-visits|update-recruiting-visits-own', ['only' => ['update']]);
        $this->middleware('permission:update-recruiting-visits', ['only' => ['dedup']]);
    }

    public function store(Request $request): JsonResponse
    {
        Log::debug(self::class.': Pre-Validation Data', $request->all());
        $validator = Validator::make($request->all(), [
            'recruiting_email' => 'required|email|max:255',
            'recruiting_name' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }

        try {
            DB::beginTransaction();
            $personInfo = $request->only(['recruiting_email', 'recruiting_name']);
            Log::debug(self::class.': New Visit Data (Pre-Store)', $personInfo);
            $visit = RecruitingVisit::create($personInfo);

            $recruitingResponses = $request->only('recruiting_responses')['recruiting_responses'];
            Log::debug(self::class.': New Visit Response Data (Pre-Store)', $recruitingResponses);

            foreach ($recruitingResponses as $response) {
                $visit->recruitingResponses()->create(['response' => $response]);
            }

            DB::commit();
            Log::info(self::class.'New Recruiting Visit Logged:', ['email' => $visit->recruiting_email]);

            return response()->json(['status' => 'success']);
        } catch (Throwable $e) {
            Bugsnag::notifyException($e);
            DB::rollback();
            Log::error('New Recruiting visit save failed', ['error' => $e->getMessage()]);

            return response()->json(['status' => 'error'])->setStatusCode(500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id RecruitingVisit ID Number
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $include = $request->input('include');
        $requestingUser = $request->user();
        $visit = RecruitingVisit::with($this->authorizeInclude(RecruitingVisit::class, $include))->find($id);
        if (null === $visit) {
            return response()->json(['status' => 'error', 'message' => 'visit_not_found'], 404);
        }

        $requestedUser = $visit->user;
        //Enforce users only viewing their own RecruitingVisit (read-recruiting-visits-own)
        if ($requestingUser->cant('read-recruiting-visits') && $requestingUser->id !== $requestedUser->id) {
            return response()->json(['status' => 'error',
                'message' => 'Forbidden - You do not have permission to view this RecruitingVisit.',
            ], 403);
        }

        return response()->json(['status' => 'success', 'visit' => new RecruitingVisitResource($visit)]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id RecruitingVisit Id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        //Update only included fields
        $this->validate($request, [
            'recruiting_name' => 'max:127',
            'recruiting_email' => 'email|max:127',
        ]);

        $visit = RecruitingVisit::find($id);
        if (! $visit) {
            return response()->json(['status' => 'error', 'message' => 'visit_not_found'], 404);
        }

        $requestingUser = $request->user();
        $requestedUser = $visit->user;
        //Enforce users only updating themselves (update-users-own)
        if ($requestingUser->cant('update-recruiting-visits') && $requestingUser->id !== $requestedUser->id) {
            return response()->json(['status' => 'error',
                'message' => 'Forbidden - You do not have permission to update this RecruitingVisit.',
            ], 403);
        }

        $visit->update($request->all());

        $visit = RecruitingVisit::with(['recruitingResponses'])->find($id);

        if (null === $visit) {
            return response()->json(['status' => 'success', 'visit' => new RecruitingVisitResource($visit)]);
        }

        return response()->json(['status' => 'error', 'message' => 'visit_not_found'], 404);
    }

    public function index(Request $request): JsonResponse
    {
        $include = $request->input('include');
        $visits = RecruitingVisit::with($this->authorizeInclude(RecruitingVisit::class, $include))->get();

        return response()->json(['status' => 'success', 'visits' => RecruitingVisitResource::collection($visits)]);
    }

    public function dedup(): void
    {
        $visits = RecruitingVisit::all();
        $emails = [];
        foreach ($visits as $visit) {
            echo 'Processing Visit '.$visit->id."<br/>\n";
            if (! in_array($visit->recruiting_email, $emails)) {
                $emails[] = $visit->recruiting_email;
            } else {
                echo 'Deleting Visit '.$visit->id."<br/>\n";
                $count = RecruitingResponse::where('recruiting_visit_id', $visit->id)->count();
                echo 'Deleting '.$count.' Responses for Visit '.$visit->id."<br/>\n";
                foreach ($visit->recruitingResponses as $response) {
                    echo 'Deleting Response '.$response->response."<br/>\n";
                    RecruitingResponse::where('recruiting_visit_id', $visit->id)->delete();
                }
                $visit->delete();
            }
        }
    }
}
