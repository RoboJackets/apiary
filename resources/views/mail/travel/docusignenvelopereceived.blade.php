Hi {!! $envelope->signedBy->preferred_first_name !!},

We received your Travel Authority Request for {{ $envelope->signable->travel->name }}. Once everyone has submitted their documents, we'll forward all of them to Georgia Tech for review and approval.

You can view your completed form within DocuSign at {{ $envelope->sender_view_url }}, or attached to a separate email from DocuSign.

@if(!$envelope->signable->is_paid)
You still need to make a ${{ intval($envelope->signable->travel->fee_amount) }} payment for the travel fee. You can pay online with a credit or debit card at {{ route('pay.travel') }}.

If you would prefer to pay by cash or check, make arrangements with {!! $envelope->signable->travel->primaryContact->full_name !!}. Write checks to Georgia Tech, with RoboJackets on the memo line. Don't forget to sign it!

@endif
@if(!$envelope->signedBy->has_emergency_contact_information)
You {{ (! $envelope->signable->is_paid) ? "also" : "still" }} need to add emergency contact information to your {{ config('app.name') }} profile at {{ route ('profile') }}.

@endif
For more information, visit {{ route('travel.index') }}. If you have any questions, please contact {!! $envelope->signable->travel->primaryContact->full_name !!}.

----

To stop receiving emails from {{ config('app.name') }}, visit @{{{ pm:unsubscribe }}}.
