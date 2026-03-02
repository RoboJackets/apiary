@extends('layouts/sponsors')

@section('title')
Resume Book | {{ config('app.name') }}
@endsection

@section('content')

<!-- <resume-book-index :users='@json($users)'></resume-book-index> -->
<resume-book-index-test :users='json_encode($users)'></resume-book-index-test>
@endsection
