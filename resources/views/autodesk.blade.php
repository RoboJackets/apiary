@extends('layouts/app')

    @section('title')
    Autodesk Access Request | {{ config('app.name') }}
@endsection

    @section('content')
    @component('layouts/title')
    Autodesk Access Request
    @endcomponent

    <p>
{{ $message }}
</p>

<a href="/">Go back to the dashboard</a>

                               @endsection
