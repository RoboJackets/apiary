@extends('layouts/app')

@section('title')
    Team Admin | {{ config('app.name') }}
@endsection

@section('content')
    @component('layouts/title')
        Teams Admin
    @endcomponent
    <div class="row">
        <div class="col-sm-12 col-md-3">
            <a href="{{ route('admin.teams.create') }}" class="btn btn-primary btn-above-table" role="button">New Team</a>
        </div>
    </div>
    <teams-admin-table>
    </teams-admin-table>

@endsection
