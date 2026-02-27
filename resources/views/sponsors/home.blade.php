@extends('layouts/sponsors')

@section('title')
Resume Book | {{ config('app.name') }}
@endsection

@section('content')

<resume-book-index :users="{{ $users }}"></resume-book-index>

@endsection
