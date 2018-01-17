@component('mail::message')

# We've received your payment!

@if($payment->method == "square")
We have received your payment of **{{$payment->amount}}** via **{{$payment->method_presentation}}** for **{{$payment->payable->for->name}}**.
@else
**{{$payment->user->name}}** accepted your payment of **{{$payment->amount}}** via **{{$payment->method_presentation}}** for **{{$payment->payable->for->name}}**.
@endif
This email constitutes a receipt - please keep it just in case!

-RoboJackets Treasurer
@endcomponent