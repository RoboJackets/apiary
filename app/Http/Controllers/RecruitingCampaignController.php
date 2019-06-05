<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireTernaryOperator,Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\RecruitingVisit;
use App\RecruitingCampaign;
use Illuminate\Http\Request;
use App\Traits\AuthorizeInclude;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\RecruitingCampaignRecipient;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Notification;
use App\Notifications\GeneralInterestNotification;
use App\Http\Requests\StoreRecruitingCampaignRequest;
use App\Http\Resources\RecruitingCampaign as RecruitingCampaignResource;

class RecruitingCampaignController extends Controller
{
    use AuthorizeInclude;

    public function __construct()
    {
        $this->middleware(['permission:send-notifications']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $include = $request->input('include');
        $rc = RecruitingCampaign::with($this->authorizeInclude(RecruitingCampaign::class, $include))->get();

        return response()->json(['status' => 'success', 'campaigns' => RecruitingCampaignResource::collection($rc)]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreRecruitingCampaignRequest  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRecruitingCampaignRequest $request): JsonResponse
    {
        // Store the campaign
        // Yes, I know there is an easier way to do this.
        $rc = new RecruitingCampaign();
        $fields = array_keys($request->all());
        foreach ($fields as $field) {
            $rc->$field = $request->input($field);
        }
        $rc->created_by = $request->user()->id;
        $rc->status = 'new';

        try {
            $rc->save();
        } catch (QueryException $e) {
            Bugsnag::notifyException($e);
            $errorMessage = $e->errorInfo[2];

            return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
        }

        // Import recipients from visits
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        $visits = RecruitingVisit::where('created_at', '>=', $start)->where('created_at', '<=', $end)->get();

        $added_recipient_emails = [];
        foreach ($visits as $v) {
            if (in_array($v->recruiting_email, $added_recipient_emails)) {
                Log::info(self::class.': Email '.$v->recruiting_email.' already in the list. Ignoring.');
            } else {
                // Add new recipient
                $rcr = new RecruitingCampaignRecipient();
                $rcr->email_address = $v->recruiting_email;
                $rcr->source = 'recruiting_visit';
                $rcr->recruiting_visit_id = $v->id;
                $rcr->recruiting_campaign_id = $rc->id;
                if (null !== $v->user_id) {
                    $rcr->user_id = $v->user_id;
                }
                $rcr->save();

                // Add to array for dedup
                $added_recipient_emails[] = $v->recruiting_email;
                Log::info(
                    self::class.': Added email '.$v->recruiting_email.' as recipient for campaign '.$rc->id
                );
            }
        }

        $db_rc = RecruitingCampaign::where('id', $rc->id)->with('recipients')->first();

        if (is_numeric($db_rc->id)) {
            return response()->json(['status' => 'success', 'campaign' => new RecruitingCampaignResource($db_rc)], 201);
        }

        return response()->json(['status' => 'error', 'message' => 'unknown_error'], 500);
    }

    /**
     * Create queue entries for email send.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function queue(int $id): JsonResponse
    {
        $delay_hours = 0;
        $rcr_q = RecruitingCampaignRecipient::where('recruiting_campaign_id', $id)->whereNull('notified_at');
        $rcr_count = $rcr_q->count();
        $rcr_q->chunk(30, static function (Collection $chunk) use (&$delay_hours): void {
            $when = Carbon::now()->addHours($delay_hours);
            Log::debug(self::class.': Scheduling chunk for delivery in '.$delay_hours.' hours at '.$when);

            // This accepts an array ($chunk) of "Notifiable" models, so it's 30 at once like M A G I C
            Notification::send($chunk, (new GeneralInterestNotification())->delay($when));

            //Bump to an additional hour for the next chunk
            $delay_hours++;
        });

        return response()->json(['status' => 'success',
            'queue_result' => ['recipients' => $rcr_count, 'chunks' => $delay_hours],
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\RecruitingCampaign  $recruitingCampaign
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(RecruitingCampaign $recruitingCampaign): JsonResponse
    {
        $rc = new RecruitingCampaignResource($recruitingCampaign);

        return response()->json(['status' => 'success', 'campaign' => $rc]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \App\RecruitingCampaign  $recruitingCampaign
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, RecruitingCampaign $recruitingCampaign): JsonResponse
    {
        return response()->json(['status' => 'error', 'error' => 'not_implemented'], 501);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\RecruitingCampaign  $recruitingCampaign
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(RecruitingCampaign $recruitingCampaign): JsonResponse
    {
        $recruitingCampaign->delete();

        return response()->json(['status' => 'success'], 201);
    }
}
