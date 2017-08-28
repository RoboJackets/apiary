@extends('layouts/app')

@section('title')
User Admin | {{ env('APP_NAME') }}
@endsection

@section('content')
@component('layouts/title')
  Edit User Data
@endcomponent

<a href="{{route('usersAdmin')}}">Back to List</a>

<user-edit-form user-uid="rstrat6"></user-edit-form>

@endsection
