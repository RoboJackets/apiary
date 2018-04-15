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
            <div class="col-xs-12 col-sm-6 col-md-4">
                <div class="card card-teams">
                    <div class="card-body">
                        <h5 class="card-title"><b><a href="{{ route('teams.show', strtolower($team->name)) }}">
                                    {{$team->name}}
                                </a></b></h5>
                        <p class="card-text">{{ $team->description }}</p>
                        <a href="{{ route('teams.show', strtolower($team->name)) }}" class="btn btn-primary">
                            More Info
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
