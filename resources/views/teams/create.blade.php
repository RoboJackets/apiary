@extends('layouts/app')

@section('title')
    Team Admin | {{ config('app.name') }}
@endsection

@section('content')
    @component('layouts/title')
        Create Team
    @endcomponent

    <team-create-form></team-create-form>

@endsection
