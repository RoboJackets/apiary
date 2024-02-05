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
            <p>You are assigned to the following upcoming travel.</p>

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
                  <td>@markdown($travel->included_with_fee)</td>
                </tr>
                @if($travel->not_included_with_fee)
                <tr>
                  <th scope="row">Not Included</th>
                  <td>@markdown($travel->not_included_with_fee)</td>
                </tr>
                @endif
              </tbody>
            </table>
            <h2>Action Items</h2>
        @if($travel->needs_docusign && !$tar_received)
            <p>Please <a href="{{ route('docusign.travel') }}">click here</a> to submit a Travel Authority Request. Georgia Tech requires this form to be submitted for all official travel.
        @endif
        @if(!$paid)
        @if($travel->needs_docusign && !$tar_received)
        <hr>
        @endif
            <p>Please pay the travel fee. You can <a href="{{ route('pay.travel') }}">click here</a> to pay with a credit or debit card online, or make arrangements with {{ $travel->primaryContact->full_name }} to pay with cash or check in person. Please write checks to Georgia Tech, and put RoboJackets on the memo line.</p>
        @endif
        @if(!$has_emergency_contact)
        @if(($travel->needs_docusign && !$tar_received) || !$paid)
            <hr>
        @endif
            <p>Please add emergency contact information to your <a href="{{ route ('profile') }}">{{ config('app.name') }} profile</a>.</p>
        @endif
        @if($paid && (!$travel->needs_docusign || $tar_received) && $has_emergency_contact)
        <p>You're all set! Contact {{ $travel->primaryContact->full_name }} if you have any questions.</p>
        @endif
        </div>
    </div>

@endsection
