@extends('layouts/app')

@section('title')
Event Admin | {{ env('APP_NAME') }}
@endsection

@section('content')
@component('layouts/title')
  Edit Event Data
@endcomponent

<a href="{{route('eventsAdmin')}}">Back to List</a>

<event-edit-form event-id="{{$id}}"></event-edit-form>

@endsection
