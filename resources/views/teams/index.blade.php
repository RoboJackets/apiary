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
            <div class="col-sm-12 col-md-6 col-lg-4 card-teams">
                <div class="card mb-3 h-100">
                    <div class="card-body">
                        <h5 class="card-title"><b>{{$team->name}}</b></h5>
                        <p class="card-text">{{ $team->description }}</p>
                    </div>
                    <div class="card-footer" style="min-height: 63px">
                        <team-membership-button :team="{{ $team }}" :user="{{ $user }}"></team-membership-button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
