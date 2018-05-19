@extends('layouts/app')

@section('title')
    Privacy Policy | {{ config('app.name') }}
@endsection

@section('content')

    @component('layouts/title')
        Privacy Policy
    @endcomponent

    <div class="row">
        <div class="col-md-12">
            <p>Your use of this application is primarily governed by the <a href="http://www.gatech.edu/privacy">Georgia Tech Privacy and Legal Notice</a>. This document describes how RoboJackets will handle your information internally and under what circumstances it may be shared outside of RoboJackets.</p>
            <p>RoboJackets may store the following information about you each time you log in, as reported by the Georgia Tech Enterprise Directory. You may opt out of this data collection by not using the application.</p>
            <ul>
                <li>Your first name</li>
                <li>Your last name</li>
                <li>Your primary Georgia Tech email address</li>
                <li>Your GTID number</li>
                <li>Your Georgia Tech username</li>
                <li>Your RoboJackets group assignnments in OrgSync</li>
            </ul>
            <p>RoboJackets may store the following information about you if you voluntarily provide it. You may opt out of this data collection by declining to provide it.</p>
            <ul>
                <li>Your personal email address</li>
                <li>Your preferred name</li>
                <li>Your phone number</li>
                <li>Your emergency contact information, including their name and phone number</li>
                <li>Your shirt and polo sizes</li>
                <li>Your gender</li>
                <li>Your ethnicity</li>
            </ul>
            <p>RoboJackets will only release information about you as strictly necessary to Georgia Tech campus departments performing work on our behalf, including but not limited to granting BuzzCard access to the shop, enrollment in SUMS, and reimbursement processing.</p>
            <p>Demographic information may be reported in aggregate to current or potential sponsors.</p>
            <p>All other information will be released only to RoboJackets officers in the course of conducting business.</p>
        </div>
    </div>

@endsection
