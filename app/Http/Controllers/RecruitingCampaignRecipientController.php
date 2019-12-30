<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecruitingCampaignRecipientRequest;
use App\Http\Requests\UpdateRecruitingCampaignRecipientRequest;
use App\Http\Resources\RecruitingCampaignRecipient as RCRResource;
use App\RecruitingCampaign;
use App\RecruitingCampaignRecipient;
use App\Traits\AuthorizeInclude;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecruitingCampaignRecipientController extends Controller
{
    use AuthorizeInclude;

    public function __construct()
    {
        $this->middleware(['permission:send-notifications']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(RecruitingCampaign $campaign, Request $request): JsonResponse
    {
        $include = $request->input('include');
        $rcr = RecruitingCampaignRecipient::with($this->authorizeInclude(RecruitingCampaignRecipient::class, $include))
            ->where('recruiting_campaign_id', $campaign->id)->get();

        return response()->json(['status' => 'success', 'recipients' => RCRResource::collection($rcr)]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RecruitingCampaign $campaign, StoreRecruitingCampaignRecipientRequest $request): JsonResponse
    {
        // Used for response
        $added_addresses = [];
        $duplicate_addresses = [];

        foreach ($request->input('recipients') as $recipient) {
            $rcr = RecruitingCampaignRecipient::firstOrNew([
                'email_address' => $recipient['email_address'],
                'recruiting_campaign_id' => $campaign->id,
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

    // phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter
    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Display the specified resource.
     */
    public function show(RecruitingCampaign $campaign, RecruitingCampaignRecipient $recipient): JsonResponse
    {
        return response()->json(['status' => 'success', 'recipient' => new RCRResource($recipient)], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        RecruitingCampaign $campaign,
        RecruitingCampaignRecipient $recipient,
        UpdateRecruitingCampaignRecipientRequest $request
    ): JsonResponse {
        $rcr = $recipient;

        $rcr->update($request->all());

        return response()->json(['status' => 'success', 'recipient' => new RCRResource($rcr)]);
    }

    // phpcs:enable

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $campaign_id, int $recipient_id): JsonResponse
    {
        $rcr = RecruitingCampaignRecipient::where('recruiting_campaign_id', $campaign_id)
            ->where('id', $recipient_id)
            ->first();

        if (null === $rcr) {
            return response()->json(['status' => 'not_found'], 404);
        }

        if (true === $rcr->delete()) {
            return response()->json(['status' => 'success'], 200);
        }

        return response()->json(['status' => 'error'], 500);
    }
}
