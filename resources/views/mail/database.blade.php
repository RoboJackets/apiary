{{-- This is for rendering Markdown emails from the database. --}}
@component('mail::message')
{{ $markdown }}
@endcomponent