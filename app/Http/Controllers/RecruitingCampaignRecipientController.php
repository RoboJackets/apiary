<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
     * @return \Illuminate\Http\Response
     */
    public function index($recruiting_campaign_id, Request $request)
    {
        // Add $r_c_i to $request to allow for validation of campaign existence
        $request['recruiting_campaign_id'] = $recruiting_campaign_id;
        $this->validate($request, [
            'recruiting_campaign_id' => 'exists:recruiting_campaigns,id|numeric',
        ]);

        $include = $request->input('include');
        $rcr = RecruitingCampaignRecipient::with($this->authorizeInclude(RecruitingCampaignRecipient::class, $include))
            ->where('recruiting_campaign_id', $recruiting_campaign_id)->get();

        return response()->json(['status' => 'success', 'recipients' => RCRResource::collection($rcr)]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  int $recruiting_campaign_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($recruiting_campaign_id, Request $request)
    {
        // Add $r_c_i to $request to allow for validation of campaign existence
        $request['recruiting_campaign_id'] = $recruiting_campaign_id;

        $this->validate($request, [
            'recruiting_campaign_id' => 'exists:recruiting_campaigns,id|numeric',
            'recipients' => 'required|array',
            'recipients.*.email_address' => 'required',
            'recipients.*.recruiting_visit_id' => 'exists:recruiting_visits,id|numeric',
            'recipients.*.user_id' => 'exists:users,id|numeric',
        ]);

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
            'recipients' => ['added' => $added_addresses, 'duplicate' => $duplicate_addresses], ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param int $recruiting_campaign_id
     * @param  int $recruiting_campaign_recipient_id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function show($campaign_id, $recipient_id, Request $request)
    {
        // Add $r_c_i and $id to $request to allow for validation
        $request['recruiting_campaign_id'] = $campaign_id;
        $request['id'] = $recipient_id;

        $this->validate($request, [
            'recruiting_campaign_id' => 'exists:recruiting_campaigns,id|numeric',
            'id' => 'exists:recruiting_campaign_recipients,id|numeric',
        ]);

        $rcr = RecruitingCampaignRecipient::where('recruiting_campaign_id', $campaign_id)
            ->where('id', $recipient_id)->first();

        return response()->json(['status' => 'success', 'recipient' => new RCRResource($rcr)], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $recruiting_campaign_id
     * @param  int $recruiting_campaign_recipient_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update($campaign_id, $recipient_id, Request $request)
    {
        // Add $r_c_i to $request to allow for validation of campaign existence
        $request['recruiting_campaign_id'] = $campaign_id;

        $this->validate($request, [
            'recruiting_campaign_id' => 'exists:recruiting_campaigns,id|numeric|nullable',
            'email_address' => 'nullable',
            'recruiting_visit_id' => 'exists:recruiting_visits,id|nullable',
            'user_id' => 'exists:users,id|numeric|nullable',
        ]);

        $rcr = RecruitingCampaignRecipient::where('recruiting_campaign_id', $campaign_id)
            ->where('id', $recipient_id)->first();

        $rcr->update($request->all());

        return response()->json(['status' => 'success', 'recipient' => new RCRResource($rcr)]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $recruiting_campaign_id
     * @param  int $recruiting_campaign_recipient_id
     * @return \Illuminate\Http\Response
     */
    public function destroy($recruiting_campaign_id, $recruiting_campaign_recipient_id)
    {
        $rcr = RecruitingCampaignRecipient::where('recruiting_campaign_id', $recruiting_campaign_id)
            ->where('id', $recruiting_campaign_recipient_id)->first();
        $rcr->delete();

        return response()->json(['status' => 'success'], 200);
    }
}
