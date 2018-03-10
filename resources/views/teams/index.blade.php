@extends('layouts/app')

@section('title')
   Teams | {{ config('app.name') }}
@endsection

@section('content')
    @component('layouts/title')
        Teams
    @endcomponent
    <div class="row">
        @foreach (\App\Team::visible()->orderBy('name', 'asc')->get() as $team)
            <div class="col-xs-12 col-sm-6 col-md-4">
                <div class="card card-teams">
                    <div class="card-body">
                        <h5 class="card-title"><b>{{ $team->name }}</b></h5>
                        <p class="card-text">{{ $team->short_description }}</p>
                        <a href="{{ route('teams.show', $team->id) }}" class="btn btn-primary">More Info</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
