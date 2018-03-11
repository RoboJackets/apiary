@extends('layouts/app')

@section('title')
    {{ $team->name }} | Teams | {{ config('app.name') }}
@endsection

@section('content')
    @component('layouts/title')
        {{$team->name}}
    @endcomponent

    <team-show :team="{{ $team }}" :user="{{ $user }}"></team-show>
@endsection