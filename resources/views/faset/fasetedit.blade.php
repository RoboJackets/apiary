@extends('layouts/app')

@section('title')
FASET Admin | {{ env('APP_NAME') }}
@endsection

@section('content')
@component('layouts/title')
  Edit FASET Response
@endcomponent

<a href="{{route('fasetAdmin')}}">Back to List</a>

<faset-edit-form faset-visit-id="{{$id}}"></faset-edit-form>

@endsection
