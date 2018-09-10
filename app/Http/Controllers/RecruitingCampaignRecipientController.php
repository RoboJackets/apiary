<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\RecruitingCampaignRecipient;

class RecruitingCampaignRecipientController extends Controller
{
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
            'campaign_id' => 'exists:recruiting_campaigns,id|numeric',
            'addresses' => 'required',
            'recruiting_visit_id' => 'exists:recruiting_visits,id',
            'user_id' => 'exists:users,id|numeric'
        ]);

        $addresses = $request->input('addresses');
        $added_addresses = [];
        $duplicate_addresses = [];

        if (is_string($addresses)) {
            // Just one address to add, so let's overwrite $addresses as a single-member array
            $addresses = [$addresses];
        }

        if (is_array($addresses)) {
            foreach ($addresses as $address) {
                $rcr = RecruitingCampaignRecipient::firstOrNew([
                    'email_address' => $address,
                    'recruiting_campaign_id' => $request->input('recruiting_campaign_id'),
                ]);

                if (isset($rcr->id)) {
                    // Model already exists
                    $duplicate_addresses[] = $rcr->email_address;
                } else {
                    // Model doesn't exist, so let's add stuff and save it
                    $rcr->source = $request->input('source', 'manual');
                    $rcr->recruiting_visit_id = $request->input('recruiting_visit_id');
                    $rcr->user_id = $request->input('recruiting_visit_id');
                    $rcr->save();
                }
            }
        } else {
            return response()->json(['status' => 'error', 'error' => 'Invalid address format - Must be array or string.'], 422);
        }

        return response()->json(['status' => 'success',
            'recipients' => ['added' => $added_addresses, 'duplicate' => $duplicate_addresses]]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\RecruitingCampaignRecipient  $recruitingCampaignRecipient
     * @return \Illuminate\Http\Response
     */
    public function show(RecruitingCampaignRecipient $recruitingCampaignRecipient)
    {
        return response()->json(['status' => 'error', 'error' => 'not_implemented'], 501);
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
        return response()->json(['status' => 'error', 'error' => 'not_implemented'], 501);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\RecruitingCampaignRecipient  $recruitingCampaignRecipient
     * @return \Illuminate\Http\Response
     */
    public function destroy(RecruitingCampaignRecipient $recruitingCampaignRecipient)
    {
        return response()->json(['status' => 'error', 'error' => 'not_implemented'], 501);
    }
}
