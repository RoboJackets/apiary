@extends('layouts/app')

@section('title')
    Personal Access Token Details | {{ config('app.name') }}
@endsection

@section('content')
    @component('layouts/title')
        Personal Access Token Details
    @endcomponent

    @if(Session::has("pat_plain_token"))
        <p>
            Successfully created a new personal access token for {{ Session::get("pat_user_name") }}. <br />
            <small>Nova Actions can't show modals with information afterwards, so instead we had to send
                you and your secrets to the Twilight Zone.</small>
        </p>

        <div class="alert alert-warning">
            Save this token now—it will not be viewable after this!
        </div>

        <p class="text-break">
            <strong>Personal Access Token:</strong>
            <textarea readonly rows="11" onclick="this.select()" class="form-control text-break">{{ Session::get("pat_plain_token") ?? "Unavailable" }}</textarea></p>
    @else
        This page's content has expired.
    @endif

    <a href="{{ url()->previous("/nova") }}">Go back to Nova</a>

@endsection
