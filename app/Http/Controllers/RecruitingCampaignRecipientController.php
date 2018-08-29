<?php

namespace App\Http\Controllers;

use App\RecruitingCampaignRecipient;
use Illuminate\Http\Request;

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
        return response()->json(['status' => 'error', 'error' => 'not_implemented'], 501);
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
