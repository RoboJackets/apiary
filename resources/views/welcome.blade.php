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
                            Access Status
                        </h4>
                        <p class="card-text">
                            While you do not have an active dues payment, you have a temporary access override to
                            various RoboJackets-managed IT systems that will expire on {{ $overrideDate }}.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>


@endsection
