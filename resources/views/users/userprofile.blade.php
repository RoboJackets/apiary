@extends('layouts/app')

@section('title')
Edit Profile | {{ config('app.name') }}
@endsection

@section('content')
@component('layouts/title')
  Edit Profile
@endcomponent

<user-edit-form user-uid="{{$id}}"></user-edit-form>

@endsection
