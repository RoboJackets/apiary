<title>@yield('title')</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="sentry-dsn" content="{{ config('sentry.dsn') }}">
<meta name="sentry-app-env" content="{{ config('app.env') }}">
<meta name="sentry-release" content="{{ config('sentry.release') }}">
@if (auth()->user())
<meta name="sentry-user-id" content="{{ auth()->user()->id }}">
<meta name="sentry-username" content="{{ auth()->user()->uid }}">
@endif
<link href="{{ mix('/css/app.css') }}" rel="stylesheet" crossorigin="anonymous">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap4.min.css">


@include('favicon')
