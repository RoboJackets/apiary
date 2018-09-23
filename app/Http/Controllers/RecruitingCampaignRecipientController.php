<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\RecruitingCampaignRecipient;

class RecruitingCampaignRecipientController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:send-notifications']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rcr = RecruitingCampaignRecipient::all();

        return response()->json(['status' => 'success', 'recipients' => $rcr]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
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
     * @param  \App\RecruitingCampaignRecipient  $recruitingCampaignRecipient
     * @return \Illuminate\Http\Response
     */
    public function show(RecruitingCampaignRecipient $recruitingCampaignRecipient)
    {
        return response()->json(['status' => 'success', 'recipient' => $recruitingCampaignRecipient], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\RecruitingCampaignRecipient  $recruitingCampaignRecipient
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RecruitingCampaignRecipient $recruitingCampaignRecipient)
    {
        $this->validate($request, [
            'recruiting_campaign_id' => 'exists:recruiting_campaigns,id|numeric|nullable',
            'email_address' => 'nullable',
            'recruiting_visit_id' => 'exists:recruiting_visits,id|nullable',
            'user_id' => 'exists:users,id|numeric|nullable',
        ]);

        $rcr = $recruitingCampaignRecipient->update($request->all());

        return response()->json(['status' => 'success', 'recipient' => $rcr]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\RecruitingCampaignRecipient  $recruitingCampaignRecipient
     * @return \Illuminate\Http\Response
     */
    public function destroy(RecruitingCampaignRecipient $recruitingCampaignRecipient)
    {
        $recruitingCampaignRecipient->delete();

        return response()->json(['status' => 'success'], 200);
    }
}
