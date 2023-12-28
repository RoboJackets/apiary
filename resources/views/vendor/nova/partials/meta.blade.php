<meta name="sentry-dsn" content="{{ config('sentry.dsn') }}">
<meta name="sentry-app-env" content="{{ config('app.env') }}">
<meta name="sentry-release" content="{{ config('sentry.release') }}">
{!! \Sentry\Laravel\Integration::sentryTracingMeta() !!}
<meta name="sentry-user-id" content="{{ auth()->user()->id }}">
<meta name="sentry-username" content="{{ auth()->user()->uid }}">
<meta name="sentry-email" content="{{ auth()->user()->gt_email }}">
<meta name="sentry-name" content="{{ auth()->user()->name }}">
