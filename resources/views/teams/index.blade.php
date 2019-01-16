@extends('layouts/app')

@section('title')
   Teams | {{ config('app.name') }}
@endsection

@section('content')
    @component('layouts/title')
        Teams
    @endcomponent
    <div class="row">
        @foreach ($teams as $team)
            <team-card :team="{{ $team }}" :user="{{ $user }}"></team-card>
        @endforeach
    </div>
@endsection
