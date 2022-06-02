Hi {{ $transaction->user->preferred_first_name }},

You still need to make a ${{ intval($transaction->package->cost) }} payment for {{ $transaction->package->name }} dues. If you would like to pay for a different term, visit {{ route('showDuesFlow') }}.

You can pay online with a credit or debit card at {{ route('pay.dues') }}. Note that we add an additional ${{ number_format(\App\Models\Payment::calculateSurcharge($transaction->package->cost * 100) / 100, 2) }} surcharge for online payments.

If you would prefer to pay by cash or check, please bring it to an officer or project manager at the shop.

Please write checks to Georgia Tech, with RoboJackets on the memo line. Don't forget to sign it!

If you have any questions, just reply to this email.
