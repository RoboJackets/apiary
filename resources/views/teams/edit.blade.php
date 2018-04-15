@extends('layouts/app')

@section('title')
    Team Admin | {{ config('app.name') }}
@endsection

@section('content')
    @component('layouts/title')
        Edit Team
    @endcomponent

    <team-edit-form team-id="{{$id}}"></team-edit-form>

@endsection
