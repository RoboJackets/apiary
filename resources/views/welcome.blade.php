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
        @if($needsTransaction)
            <div class="col-sm-6 col-md-3 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Complete Profile
                        </h4>
                        <p class="card-text">
                            To continue your membership in RoboJackets, we need you to update your information and pay dues.
                        </p>
                        <a href="/dues">Update Info Now</a>
                    </div>
                </div>
            </div>
        @endif
        @if($needsPayment)
            <div class="col-sm-6 col-md-3 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Pay Dues
                        </h4>
                        <p class="card-text">
                            Dues payments are accepted online with credit/debit cards or in-person with card/cash/check.
                        </p>
                        <a style="margin-bottom: 0.5rem;display:block" href="/dues/pay">Pay Online Now</a>
                        <a href="/dues">Change Dues Term</a>
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
        @if($needsResume)
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
                            GitHub Invite Pending
                        </h4>
                        <p class="card-text">
                            You have been invited to RoboJackets' GitHub organization, but you must accept the invitation before you can access any repositories.
                        </p>
                        <a href="https://github.com/orgs/RoboJackets/invitation">Accept Your Invitation</a>
                    </div>
                </div>
            </div>
        @endif
    </div>


@endsection
