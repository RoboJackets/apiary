@extends('layouts/app')

@section('title')
    Error | {{ config('app.name') }}
@endsection

@section('content')

    @component('layouts/title')
        You need to sign a membership agreement!
    @endcomponent

    <div class="row">
        <div class="col-md-12">
            You need to sign our membership agreement before you can pay dues. <a href="{{ route('agreement.render') }}">Click here</a> to sign electronically or <a href="{{ route('agreement.print') }}">click here</a> for a printable version.
        </div>
    </div>

@endsection
