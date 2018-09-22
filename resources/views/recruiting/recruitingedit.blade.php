@extends('layouts/app')

@section('title')
Recruiting Admin | config('app.name') }}
@endsection

@section('content')
@component('layouts/title')
  Edit Recruiting Response
@endcomponent

<a href="{{route('recruitingAdmin')}}">Back to List</a>

<recruiting-edit-form recruiting-visit-id="{{$id}}"></recruiting-edit-form>

@endsection
