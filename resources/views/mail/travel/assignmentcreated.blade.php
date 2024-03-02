Hi {!! $assignment->user->preferred_first_name !!},

@if($assignment->travel->needs_docusign)
You have been assigned to {{ $assignment->travel->name }}. Complete the following tasks as soon as possible so that we can book travel arrangements for you.

Visit {{ route('docusign.travel') }} to submit {{ $assignment->travel->needs_airfare_form ? ($assignment->travel->needs_travel_information_form ? 'forms' : 'an airfare request form') : ($assignment->travel->needs_travel_information_form ? 'a travel information form' : '') }} for your trip.

Make a ${{ intval($assignment->travel->fee_amount) }} payment for the trip fee. You can pay online with a credit or debit card at {{ route('pay.travel') }}.
@else
You have been assigned to {{ $assignment->travel->name }}. Pay the ${{ intval($assignment->travel->fee_amount) }} trip fee as soon as possible{{ $assignment->travel->return_date > \Carbon\Carbon::now() ? ' so that we can book travel arrangements for you' : '' }}. You can pay online with a credit or debit card at {{ route('pay.travel') }}.
@endif

If you would prefer to pay by cash or check, make arrangements with {!! $assignment->travel->primaryContact->full_name !!}. Write checks to Georgia Tech, with RoboJackets on the memo line. Don't forget to sign it!
@if(!$assignment->user->has_emergency_contact_information && $assignment->travel->return_date > \Carbon\Carbon::now())

You also need to add emergency contact information to your {{ config('app.name') }} profile at {{ route ('profile') }}.
@endif

For more information, visit {{ route('travel.index') }}. If you have any questions, contact {!! $assignment->travel->primaryContact->full_name !!}.

----

To stop receiving emails from {{ config('app.name') }}, visit @{{{ pm:unsubscribe }}}.
