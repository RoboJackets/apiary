<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateRecruitingCampaignRecipientRequest;
use App\Http\Requests\ShowRecruitingCampaignRecipientRequest;
use App\Http\Requests\StoreRecruitingCampaignRecipientRequest;
use App\Http\Requests\IndexRecruitingCampaignRecipientRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\RecruitingCampaignRecipient;
use App\Http\Resources\RecruitingCampaignRecipient as RCRResource;

class RecruitingCampaignRecipientController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:send-notifications']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param int $recruiting_campaign_id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(int $recruiting_campaign_id, IndexRecruitingCampaignRecipientRequest $request): JsonResponse
    {
        // Add $r_c_i to $request to allow for validation of campaign existence
        $request['recruiting_campaign_id'] = $recruiting_campaign_id;

        $include = $request->input('include');
        $rcr = RecruitingCampaignRecipient::with($this->authorizeInclude(RecruitingCampaignRecipient::class, $include))
            ->where('recruiting_campaign_id', $recruiting_campaign_id)->get();

        return response()->json(['status' => 'success', 'recipients' => RCRResource::collection($rcr)]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param int $recruiting_campaign_id
     * @param \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(int $recruiting_campaign_id, StoreRecruitingCampaignRecipientRequest $request): JsonResponse
    {
        // Add $r_c_i to $request to allow for validation of campaign existence
        $request['recruiting_campaign_id'] = $recruiting_campaign_id;


        // Used for response
        $added_addresses = [];
        $duplicate_addresses = [];

        foreach ($request->input('recipients') as $recipient) {
            $rcr = RecruitingCampaignRecipient::firstOrNew([
                'email_address' => $recipient['email_address'],
                'recruiting_campaign_id' => $request->input('recruiting_campaign_id'),
            ]);

            if (isset($rcr->id)) {
                // Model already exists
                $duplicate_addresses[] = $rcr->email_address;
            } else {
                // Model doesn't exist, so let's add stuff and save it
                $rcr->source = $recipient['source'] ?? 'manual';
                $rcr->recruiting_visit_id = $recipient['recruiting_visit_id'] ?? null;
                $rcr->user_id = $recipient['user_id'] ?? null;
                $rcr->save();
                $added_addresses[] = $recipient['email_address'];
            }
        }

        return response()->json(['status' => 'success',
            'recipients' => ['added' => $added_addresses, 'duplicate' => $duplicate_addresses],
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param int $campaign_id
     * @param int $recipient_id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $campaign_id, int $recipient_id, ShowRecruitingCampaignRecipientRequest $request): JsonResponse
    {
        // Add $r_c_i and $id to $request to allow for validation
        $request['recruiting_campaign_id'] = $campaign_id;
        $request['id'] = $recipient_id;


        $rcr = RecruitingCampaignRecipient::where('recruiting_campaign_id', $campaign_id)
            ->where('id', $recipient_id)
            ->first();

        return response()->json(['status' => 'success', 'recipient' => new RCRResource($rcr)], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $campaign_id
     * @param int $recipient_id
     * @param \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(int $campaign_id, int $recipient_id, UpdateRecruitingCampaignRecipientRequest $request): JsonResponse
    {
        // Add $r_c_i to $request to allow for validation of campaign existence
        $request['recruiting_campaign_id'] = $campaign_id;


        $rcr = RecruitingCampaignRecipient::where('recruiting_campaign_id', $campaign_id)
            ->where('id', $recipient_id)
            ->first();

        $rcr->update($request->all());

        return response()->json(['status' => 'success', 'recipient' => new RCRResource($rcr)]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $campaign_id
     * @param int $recipient_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $campaign_id, int $recipient_id): JsonResponse
    {
        $rcr = RecruitingCampaignRecipient::where('recruiting_campaign_id', $campaign_id)
            ->where('id', $recipient_id)
            ->first();
        $rcr->delete();

        return response()->json(['status' => 'success'], 200);
    }
}
