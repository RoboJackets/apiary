<title>@yield('title')</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="sentry-dsn" content="{{ config('sentry.dsn') }}">
<meta name="sentry-app-env" content="{{ config('app.env') }}">
<meta name="sentry-release" content="{{ config('sentry.release') }}">
{!! \Sentry\Laravel\Integration::sentryTracingMeta() !!}
@if (auth()->user())
<meta name="sentry-user-id" content="{{ auth()->user()->id }}">
<meta name="sentry-username" content="{{ auth()->user()->uid }}">
@endif
<link href="{{ mix('/css/app.css') }}" rel="stylesheet">


@include('favicon')
