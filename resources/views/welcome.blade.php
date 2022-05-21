@extends('layouts/app')

@section('title')
    {{ config('app.name') }}
@endsection

@section('content')

    @component('layouts/title')
        Dashboard
    @endcomponent

    <div class="row">
        <div class="col-sm-6 col-md-3 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">
                        Welcome back, {{ $preferredName }}!
                    </h4>
                    <p class="card-text">
                        @if($isNew)
                            <b>Status:</b> Non-Member<br/>
                        @elseif($status)
                            <b>Status:</b> Active<br/>
                            <b>Until:</b> {{ $packageEnd }}<br/>
                            <b>Member Since:</b> {{ $firstPayment }}
                        @else
                            <b>Status:</b> Inactive<br/>
                            <b>Since:</b> {{ $packageEnd }}<br/>
                            <b>Member Since:</b> {{ $firstPayment }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
        @if(!$signedLatestAgreement && $agreementExists)
            <div class="col-sm-6 col-md-3 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        @if(!$signedAnyAgreement)
                            <h4 class="card-title">
                                Start Here
                            </h4>
                            <p class="card-text">
                                Please review and sign the RoboJackets membership agreement. This document describes our expectations for your behavior in our facility, when traveling, and when representing RoboJackets.
                            </p>
                        @else
                            <h4 class="card-title">
                                Updated Membership Agreement
                            </h4>
                            <p class="card-text">
                                The RoboJackets membership agreement has changed since the last time you signed it.  Please review and sign the updated version to continue your membership.
                            </p>
                        @endif
                            <a href="{{ route('agreement.render') }}">Sign Electronically</a> or <a href="{{ route('agreement.print') }}">Print</a>
                    </div>
                </div>
            </div>
        @endif
        @if($signedLatestAgreement && $needsTransaction)
            <div class="col-sm-6 col-md-3 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Next Steps
                        </h4>
                        <p class="card-text">
                            To continue your membership in RoboJackets, we need you to update your information and pay dues.
                        </p>
                        <a href="{{ route('showDuesFlow') }}">Pay Dues</a>
                    </div>
                </div>
            </div>
        @endif
        @if($signedLatestAgreement && $needsPayment)
            <div class="col-sm-6 col-md-3 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Pay Dues
                        </h4>
                        <p class="card-text">
                            @if (config('features.card-present-payments'))
                            Dues payments are accepted online with credit/debit cards or in-person with card/cash/check.
                            @else
                            Dues payments are accepted online with credit/debit cards or in-person with cash/check.
                            @endif
                        </p>
                        <a style="margin-bottom: 0.5rem;display:block" href="{{ route('pay.dues') }}">Pay Online Now</a>
                        <a href="{{ route('showDuesFlow') }}">Change Dues Term</a>
                    </div>
                </div>
            </div>
        @endif
        @if($hasOverride)
            <div class="col-sm-6 col-md-3 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Access Override
                        </h4>
                        <p class="card-text">
                            While you have not paid dues for this semester, you have temporary access to RoboJackets
                            systems until {{ $overrideDate }}. If you have questions or need an extension, please ask
                            in <a href="https://slack.com/app_redirect?team=T033JPZLT&channel=C29Q3D8K0">#it-helpdesk</a>.
                        </p>
                    </div>
                </div>
            </div>
        @endif
        @if($hasExpiredOverride)
            <div class="col-sm-6 col-md-3 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Access Override
                        </h4>
                        <p class="card-text">
                            Your temporary access to RoboJackets systems expired on {{ $overrideDate }}. If you have questions or need an extension, please ask in <a href="https://slack.com/app_redirect?team=T033JPZLT&channel=C29Q3D8K0">#it-helpdesk</a>.
                        </p>
                    </div>
                </div>
            </div>
        @endif
        @if($signedLatestAgreement && $needsResume)
            <div class="col-sm-6 com-md-3 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Upload Your Resume
                        </h4>
                        <p class="card-text">
                            A benefit of being an active member of RoboJackets is being a part of our resume book we provide to sponsors. Please make sure it is always up to date.
                        </p>
                        <a href="/resume">Upload Your Resume</a>
                    </div>
                </div>
            </div>
        @endif
        @if($githubInvitePending)
            <div class="col-sm-6 com-md-3 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            GitHub Invitation Pending
                        </h4>
                        <p class="card-text">
                            You have been invited to RoboJackets' GitHub organization, but you must accept the invitation before you can access any repositories.
                        </p>
                        <a href="https://github.com/orgs/RoboJackets/invitation">Accept Your Invitation</a>
                    </div>
                </div>
            </div>
        @endif
        @if($sumsAccessPending)
            <div class="col-sm-6 com-md-3 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            SUMS Access Notice
                        </h4>
                        <p class="card-text">
                            You don't have a SUMS account yet. If you will need access to machining equipment, <a href="https://sums.gatech.edu/Login2.aspx?LP=Users" target="_blank">log in to SUMS</a> to create an account, then <a href="/sums">request access</a>.
                        </p>
                    </div>
                </div>
            </div>
        @endif
        @if($signedLatestAgreement && !$needsTransaction && !$needsPayment)
        @if($needTravelPayment)
            <div class="col-sm-6 com-md-3 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Travel Payment Required
                        </h4>
                        <p class="card-text">
                            You need to make a payment for {{ $travelName }}.
                        </p>
                        <a href="{{ route('pay.travel') }}">Pay Online Now</a> or <a href="{{ route('travel.index') }}">View Travel</a>
                    </div>
                </div>
            </div>
        @endif
        @if($needTravelAuthorityRequest)
            <div class="col-sm-6 com-md-3 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Travel Authority Request Required
                        </h4>
                        <p class="card-text">
                            You need to submit a Travel Authority Request for {{ $travelName }}.
                        </p>
                        <a href="{{ $travelAuthorityRequestUrl }}">Submit Now</a> or <a href="{{ route('travel.index') }}">View Travel</a>
                    </div>
                </div>
            </div>
        @endif
        @endif
    </div>


@endsection
