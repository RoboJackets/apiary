@component('mail::message')

# You're almost done!

We've received your RoboJackets dues form! 

Next, you need to make a payment for **${{$duesPackage->cost}}**.

When completing the form, you chose the **{{$duesPackage->name}}** dues package. If this was selected in error, simply return to the [dues form]({{ route('payDues')}}) and select the proper dues package. Notify the person collecting your dues that you have multiple pending requests.

## We accept the following forms of payment:
1. Online via Debit card at [Square Cash](cash.me/$RoboJackets) - add your GT username ({{$uid}}) in the note
1. Cash or check - pay an officer or project manager at the shop.
1. Credit card - pay an officer or project manager at the shop. Note that we will add a $3 surcharge to cover payment processing fees.

-RoboJackets Treasurer

@endcomponent
