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
                        Welcome{{ $signedAnyAgreement ? " back" : "" }}, {{ $preferredName }}!
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
                                Review and sign the RoboJackets membership agreement. It describes our expectations for your behavior in our facility, when traveling, and when representing RoboJackets.
                            </p>
                        @else
                            <h4 class="card-title">
                                Updated Membership Agreement
                            </h4>
                            <p class="card-text">
                                The RoboJackets membership agreement has changed since the last time you signed it. Review and sign the updated version to continue your membership.
                            </p>
                        @endif
                        <a href="{{ route('docusign.agreement') }}">Sign Electronically</a>
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
                            systems until {{ $overrideDate }}. If you have questions or need an extension, ask
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
                            Your temporary access to RoboJackets systems expired on {{ $overrideDate }}. If you have questions or need an extension, ask in <a href="https://slack.com/app_redirect?team=T033JPZLT&channel=C29Q3D8K0">#it-helpdesk</a>.
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
                            A benefit of being an active member of RoboJackets is being a part of our resume book we provide to sponsors. Make sure it is always up to date.
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
        @if($clickupInvitePending)
        <div class="col-sm-6 com-md-3 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Clickup Invitation Pending
                        </h4>
                        <p class="card-text">
                            You have been invited to the RoboJackets Clickup page. Please go to Clickup using the link below and accept your invitation.
                        </p>
                        <a href="https://app.clickup.com">Accept Your Invitation</a>
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
        @if($travelAssignment && (!$signedLatestAgreement || (!$status && $needsTransaction) || (!$status && $needsPayment) || ($travelAssignment->needs_docusign) || (!$travelAssignment->is_paid) || (!$hasEmergencyContactInformation)))
            <div class="col-sm-6 com-md-3 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Action Required for Travel
                        </h4>
                        <p class="card-text">
                            You have been assigned to {{ $travelAssignment->travel->name }}. Complete the tasks on the Travel tab so that we can book travel for you.
                        </p>
                        <a href="{{ route('travel.index') }}">View Travel</a>
                    </div>
                </div>
            </div>
        @endif
        <self-service-access-override />
    </div>
@endsection
