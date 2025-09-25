The following dues {{ count($packages) === 1 ? 'deadline is' : 'deadlines are' }} occurring within the next 7 days:

@foreach($packages as $package)
- {{ $package->name }} expires on {{ $package->access_end->format('l, F j') }}
@endforeach

{{ $userCount }} {{ \Illuminate\Support\Str::plural('member', $userCount) }} will lose access to RoboJackets systems, unless they renew their {{ \Illuminate\Support\Str::plural('membership', $userCount) }} before the expiration {{ \Illuminate\Support\Str::plural('date', count($packages)) }}.

You may update the "Access End Date" for {{ count($packages) === 1 ? 'this package' : 'these packages' }} at {{ count($packages) === 1 ? route('nova.pages.detail', ['resource' => \App\Nova\DuesPackage::uriKey(), 'resourceId' => $packages->sole()->id]) : route('nova.pages.index', ['resource' => \App\Nova\DuesPackage::uriKey()]) }}. If the {{ count($packages) === 1 ? 'date is' : 'dates are' }} correct, you may ignore this email.

Read more about configuring dues at {{ config('app.url') }}/docs/officers/dues/.

----

You are receiving this email because you are an officer or administrator. To stop receiving these emails, contact RoboJackets IT at support@robojackets.org.
