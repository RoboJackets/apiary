Hi {!! $assignment->user->preferred_first_name !!},

@if(!$assignment->tar_received && $assignment->travel->tar_required)
You still need to submit a Travel Authority Request for {{ $assignment->travel->name }}. Please visit {{ route('docusign.travel') }} to fill out and sign it.

@endif
@if(!$assignment->is_paid)
You{{ (!$assignment->tar_received && $assignment->travel->tar_required) ? ' also': '' }} still need to make a ${{ intval($assignment->travel->fee_amount) }} payment{{ (!$assignment->tar_received && $assignment->travel->tar_required) ? '' : ' for '.$assignment->travel->name }}. You can pay online with a credit or debit card at {{ route('pay.travel') }}.

If you would prefer to pay by cash or check, make arrangements with {!! $assignment->travel->primaryContact->full_name !!}. Write checks to Georgia Tech, with RoboJackets on the memo line. Don't forget to sign it!

@endif
@if(!$assignment->user->has_emergency_contact_information)
You {{ (! $assignment->is_paid || (!$assignment->tar_received && $assignment->travel->tar_required)) ? "also" : "still" }} need to add emergency contact information to your {{ config('app.name') }} profile at {{ route ('profile') }}.

@endif
For more information, visit {{ route('travel.index') }}. If you have any questions, please contact {{ $assignment->travel->primaryContact->full_name }}.

----

To stop receiving emails from {{ config('app.name') }}, visit @{{{ pm:unsubscribe }}}.
