Hi {{ $payment->payable->user->preferred_first_name }},

@if($payment->method === "square")
We received your online payment of ${{ number_format($payment->amount, 2) }} for {{ $payable_name }}.

You can view more details about your payment at {{ $payment->receipt_url }}.
@else
{{ $payment->user->name }} accepted your ${{ number_format($payment->amount, 2) }} {{ $payment->method }} payment for {{ $payable_name }}.
@endif

Please keep this email for your records.
