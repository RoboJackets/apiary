@component('mail::message')

RoboJackets' attendance report from {{ $export->start_time->format('l, n/j/Y \a\t g:iA') }} to {{ $export->end_time->format('l, n/j/Y \a\t g:iA') }} is available [here]({{ route('attendance.export', ['secret' => $export->secret]) }}). That link will only work once and will expire after one week.

@endcomponent
