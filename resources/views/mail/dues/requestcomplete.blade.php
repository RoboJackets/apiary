@component('mail::message')

# You're almost done!

We've received your RoboJackets dues form! 

Next, you need to make a payment for **${{$duesPackage->cost}}**.

When completing the form, you chose the **{{$duesPackage->name}}** dues package. If this was selected in error, simply return to the [dues form]({{ route('payDues')}}) and select the proper dues package. Notify the person collecting your dues that you have multiple pending requests.

## We accept the following forms of payment:
1. Credit/Debit Card - Pay [online]({{ route('dues.payOne') }}) now. Prefer to pay in-person? See an officer at the shop. *$3 fee applies to cover payment processing fees.*
1. Cash - Pay an officer or project manager at the shop.
2. Check - Make a check out to RoboJackets and give it to an officer or your PM. Don't forget to sign it!

-RoboJackets Treasurer

@endcomponent
