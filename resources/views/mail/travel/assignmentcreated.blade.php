Hi {!! $assignment->user->preferred_first_name !!},

@if($assignment->travel->tar_required)
You have been assigned to {{ $assignment->travel->name }}. Please complete the following items as soon as possible so that we can book travel for you.

Visit {{ route('docusign.travel') }} to submit a Travel Authority Request. Georgia Tech requires this form to be submitted for all official travel.

Make a ${{ intval($assignment->travel->fee_amount) }} payment for the travel fee. You can pay online with a credit or debit card at {{ route('pay.travel') }}.
@else
You have been assigned to {{ $assignment->travel->name }}. Please pay the ${{ intval($assignment->travel->fee_amount) }} travel fee as soon as possible so that we can book travel for you. You can pay online with a credit or debit card at {{ route('pay.travel') }}. Note that we add an additional ${{ number_format(\App\Models\Payment::calculateSurcharge($assignment->travel->fee_amount * 100) / 100, 2) }} surcharge for online payments.
@endif

If you would prefer to pay by cash or check, make arrangements with {!! $assignment->travel->primaryContact->full_name !!}. Write checks to Georgia Tech, with RoboJackets on the memo line. Don't forget to sign it!
@if(!$assignment->user->has_emergency_contact_information)

You also need to add emergency contact information to your {{ config('app.name') }} profile at {{ route ('profile') }}.
@endif

For more information, visit {{ route('travel.index') }}. If you have any questions, please contact {!! $assignment->travel->primaryContact->full_name !!}.

----

To stop receiving emails from {{ config('app.name') }}, visit @{{{ pm:unsubscribe }}}.
