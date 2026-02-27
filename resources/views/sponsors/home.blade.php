@extends('layouts/sponsors')

@section('title')
Resume Book | {{ config('app.name') }}
@endsection

@section('content')

<resume-book-index :users='@json($users)'></resume-book-index>

@endsection
