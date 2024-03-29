Hi {!! $user->preferred_first_name !!},

@if(\App\Models\DuesPackage::userCanPurchase($user)->count() > 1)
You still need to pay dues for this semester. Visit {{ route('home') }} to get started.

@php
print(implode(" and ", \App\Models\DuesPackage::userCanPurchase($user)->orderByDesc('cost')->get()->map(static fn (\App\Models\DuesPackage $package, int $key): string => $package->name.' dues are $'.intval($package->cost) )->toArray()))
@endphp. You may pay online with a credit or debit card.
@else
You still need to pay dues for {{ \App\Models\DuesPackage::userCanPurchase($user)->sole()->name }}. Visit {{ route('home') }} to get started.

Dues are ${{ intval(\App\Models\DuesPackage::userCanPurchase($user)->sole()->cost) }} and may be paid online with a credit or debit card.
@endif

If you would prefer to pay by cash or check, bring it to an officer or project manager at the shop. Write checks to Georgia Tech, with RoboJackets on the memo line. Don't forget to sign it!

If you have any questions, just reply to this email.

----

To stop receiving emails from {{ config('app.name') }}, visit @{{{ pm:unsubscribe }}}.
