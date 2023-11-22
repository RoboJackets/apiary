Hi {!! $transaction->user->preferred_first_name !!},

You still need to make a ${{ intval($transaction->package->cost) }} payment for {{ $transaction->package->name }} dues. You can pay online with a credit or debit card at {{ route('pay.dues') }}.

If you would prefer to pay by cash or check, please bring it to an officer or project manager at the shop. Write checks to Georgia Tech, with RoboJackets on the memo line. Don't forget to sign it!

@if(\App\Models\DuesPackage::where('dues_packages.id', '!=', $transaction->package->id)->userCanPurchase($transaction->user)->exists())
If you would prefer to pay for {{ \App\Models\DuesPackage::where('dues_packages.id', '!=', $transaction->package->id)->userCanPurchase($transaction->user)->sole()->name }} instead, visit {{ route('showDuesFlow') }}.

@endif
If you have any questions, just reply to this email.

----

To stop receiving emails from {{ config('app.name') }}, visit @{{{ pm:unsubscribe }}}.
