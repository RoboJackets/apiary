@component('mail::message')

# We've received your payment!

**{{$payment->user->name}}** accepted your payment of **{{$payment->amount}}** via **{{$payment->method_presentation}}** for **{{$payment->payable->for->name}}**.
This email constitutes a receipt - please keep it just in case!

-RoboJackets Treasurer
@endcomponent