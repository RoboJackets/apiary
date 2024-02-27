Hi {!! $payment->payable->user->preferred_first_name !!},

@if($payment->method === "square")
We received your online payment of ${{ number_format($payment->amount, 2) }} for {{ $payable_name }}.

You can view more details about your payment at {{ $payment->receipt_url }}.
@else
{!! $payment->user->name !!} accepted your ${{ number_format($payment->amount, 2) }} {{ $payment->method }} payment for {{ $payable_name }}.
@endif
@if($payment->payable_type === \App\Models\TravelAssignment::getMorphClassStatic() && !$payment->payable->tar_received && $payment->payable->travel->needs_docusign)

You still need to submit {{ $payment->payable->travel->needs_airfare_form ? ($payment->payable->travel->needs_travel_information_form ? 'forms' : 'an airfare request form') : ($payment->payable->travel->needs_travel_information_form ? 'a travel information form' : '') }} for your trip. Please visit {{ route('docusign.travel') }} to fill out and sign {{ ($payment->payable->travel->needs_airfare_form && $payment->payable->travel->needs_travel_information_form) ? 'them' : 'it' }}.
@endif
@if($payment->payable_type === \App\Models\TravelAssignment::getMorphClassStatic() && !$payment->payable->user->has_emergency_contact_information)

You {{ (!$payment->payable->tar_received && $payment->payable->travel->needs_docusign) ? "also" : "still" }} need to add emergency contact information to your {{ config('app.name') }} profile at {{ route ('profile') }}.
@endif

Please keep this email for your records.

----

To stop receiving emails from {{ config('app.name') }}, visit @{{{ pm:unsubscribe }}}.
