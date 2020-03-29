@component('mail::message')

RoboJackets' attendance report from {{ $export->start_time }} to {{ $export->end_time }} is available [here]({{ route('attendance.export', ['secret' => $export->secret]) }}). That link will only work once and will expire after one week.

@endcomponent
