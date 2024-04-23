@extends('layouts/app')

@section('title')
Travel | {{ config('app.name') }}
@endsection

@section('content')
    @component('layouts/title')
        Travel
    @endcomponent

    <div class="row">
        <div class="col-md-12">
            <p>You are assigned to the following upcoming trip.</p>

            <table class="table table-sm">
              <tbody>
                <tr>
                  <th scope="row">Name</th>
                  <td>{{ $travel->name }}</td>
                </tr>
                <tr>
                  <th scope="row">Destination</th>
                  <td>{{ $travel->destination }}</td>
                </tr>
                <tr>
                  <th scope="row">Primary Contact</th>
                  <td>{{ $travel->primaryContact->full_name }}</td>
                </tr>
                <tr>
                  <th scope="row">Departure Date</th>
                  <td>{{ $travel->departure_date->format("l, F j, Y") }}</td>
                </tr>
                <tr>
                  <th scope="row">Return Date</th>
                  <td>{{ $travel->return_date->format("l, F j, Y") }}</td>
                </tr>
                <tr>
                  <th scope="row">Fee</th>
                  <td>${{ $travel->fee_amount }}</td>
                </tr>
                <tr>
                  <th scope="row">Costs Included</th>
                  <td>{{ $travel->included_with_fee }}</td>
                </tr>
                @if($travel->not_included_with_fee)
                <tr>
                  <th scope="row">Not Included</th>
                  <td>{{ $travel->not_included_with_fee }}</td>
                </tr>
                @endif
              </tbody>
            </table>
            <h2>Action Items</h2>
        @if($needs_agreement)
            <p><a href="{{ route('docusign.agreement') }}">Click here</a> to sign the latest membership agreement.</p>
        @endif
        @if($needs_dues)
            @if($needs_agreement)
                <hr>
            @endif
            <p><a href="{{ route('home') }}">Click here</a> to pay dues.</p>
        @endif
        @if($travel->needs_docusign && !$tar_received)
            @if($needs_dues || $needs_agreement)
                <hr>
            @endif
            <p><a href="{{ route('docusign.travel') }}">Click here</a> to submit {{ $travel->needs_airfare_form ? ($travel->needs_travel_information_form ? 'forms' : 'an airfare request form') : ($travel->needs_travel_information_form ? 'a travel information form' : '') }}. Georgia Tech requires {{ ($travel->needs_airfare_form && $travel->needs_travel_information_form) ? 'these forms' : 'this form' }} to be submitted for all official travel.
        @endif
        @if(!$paid)
        @if(($travel->needs_docusign && !$tar_received) || $needs_dues || $needs_agreement)
        <hr>
        @endif
            <p>Pay the trip fee. You can <a href="{{ route('pay.travel') }}">click here</a> to pay with a credit or debit card online, or make arrangements with {{ $travel->primaryContact->full_name }} to pay with cash or check in person. Write checks to Georgia Tech, and put RoboJackets on the memo line.</p>
        @endif
        @if($needs_profile_information)
        @if(($travel->needs_docusign && !$tar_received) || !$paid || $needs_dues || $needs_agreement)
            <hr>
        @endif
            <p>Add required information to your <a href="{{ route ('profile') }}">{{ config('app.name') }} profile</a>.</p>
        @endif
        @if($paid && (!$travel->needs_docusign || $tar_received) && ! $needs_profile_information && ! $needs_agreement && !$needs_dues)
        <p>You're all set! Contact {{ $travel->primaryContact->full_name }} if you have any questions.</p>
        @endif
        </div>
    </div>

@endsection
