@component('mail::message')

RoboJackets' attendance report from {{ $export->start_time->format('l, n/j/Y \a\t g:iA') }} to {{ $export->end_time->format('l, n/j/Y \a\t g:iA') }} is available [here]({{ route('attendance.export', ['secret' => $export->secret]) }}). That link will only work once and will expire on {{ $export->expires_at->format('l, n/j/Y \a\t g:iA') }}. For help or to get a new link, please reply to this email.

@endcomponent
