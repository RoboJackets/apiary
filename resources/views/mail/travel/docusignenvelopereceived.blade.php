Hi {!! $envelope->signedBy->preferred_first_name !!},

We received your {{ $envelope->signable->travel->needs_airfare_form ? ($envelope->signable->travel->needs_travel_information_form ? 'forms' : 'airfare request form') : ($envelope->signable->travel->needs_travel_information_form ? 'travel information form' : '') }} for {{ $envelope->signable->travel->name }}. Once everyone has submitted their forms, we'll forward all of them to Georgia Tech for review and approval.

You can view your completed {{ $envelope->signable->travel->needs_airfare_form ? ($envelope->signable->travel->needs_travel_information_form ? 'forms' : 'form') : ($envelope->signable->travel->needs_travel_information_form ? 'form' : '') }} within DocuSign at {{ $envelope->sender_view_url }}, or attached to a separate email from DocuSign.

@if(!$envelope->signable->is_paid)
You still need to make a ${{ intval($envelope->signable->travel->fee_amount) }} payment for the trip fee. You can pay online with a credit or debit card at {{ route('pay.travel') }}.

If you would prefer to pay by cash or check, make arrangements with {!! $envelope->signable->travel->primaryContact->full_name !!}. Write checks to Georgia Tech, with RoboJackets on the memo line. Don't forget to sign it!

@endif
@if(((!$envelope->signable->user->has_emergency_contact_information) || ($envelope->signable->travel->needs_airfare_form && ($envelope->signable->user->legal_gender === null || $envelope->signable->user->date_of_birth === null))))
You {{ (! $envelope->signable->is_paid) ? "also" : "still" }} need to add required information to your {{ config('app.name') }} profile at {{ route ('profile') }}.

@endif
For more information, visit {{ route('travel.index') }}. If you have any questions, contact {!! $envelope->signable->travel->primaryContact->full_name !!}.

----

To stop receiving emails from {{ config('app.name') }}, visit @{{{ pm:unsubscribe }}}.
