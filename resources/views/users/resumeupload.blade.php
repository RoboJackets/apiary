@extends('layouts/app')

@section('title')
Resume | {{ config('app.name') }}
@endsection

@section('content')
@component('layouts/title')
  Resume
@endcomponent

<resume-upload-form user-uid="{{$id}}" message="{{request()->query('resume_error')}}"></resume-upload-form>

@endsection
