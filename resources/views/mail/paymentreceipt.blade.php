Hi {!! $payment->payable->user->preferred_first_name !!},

@if($payment->method === "square")
We received your online payment of ${{ number_format($payment->amount, 2) }} for {{ $payable_name }}.

You can view more details about your payment at {{ $payment->receipt_url }}.
@else
{!! $payment->user->name !!} accepted your ${{ number_format($payment->amount, 2) }} {{ $payment->method }} payment for {{ $payable_name }}.
@endif
@if($payment->payable_type === \App\Models\TravelAssignment::getMorphClassStatic() && !$payment->payable->tar_received && $payment->payable->travel->needs_docusign)

You still need to submit a Travel Authority Request. Please visit {{ route('docusign.travel') }} to fill out and sign it.
@endif

Please keep this email for your records.

----

To stop receiving emails from {{ config('app.name') }}, visit @{{{ pm:unsubscribe }}}.
