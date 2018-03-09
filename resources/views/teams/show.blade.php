@extends('layouts/app')

@section('title')
    Team Name | Teams | {{ config('app.name') }}
@endsection

@section('content')
    @component('layouts/title')
        Team Name
    @endcomponent
@endsection
