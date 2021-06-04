@extends('layouts/app')

@section('title')
    OAuth2 Client Details | {{ config('app.name') }}
@endsection

@section('content')
    @component('layouts/title')
        OAuth2 Client Details
    @endcomponent

    @if(Session::has("client_id"))
        <p>
            Your OAuth2 client was successfully created, and the client credentials are shown below.
        </p>

        @if(Session::has("client_plain_secret"))
            <p>
                <strong>Save the client secret now, as it will not be viewable
                    in plain text after this!</strong>
            </p>
        @endif

        <p>
            <strong>Client ID:</strong> {{ Session::get("client_id") }}
        </p>

        <p>
            @if(Session::get("client_confidential"))
                <strong>Client Secret:</strong> {{ Session::get("client_plain_secret") ?? "Unavailable" }}
            @else
                <strong>Client Secret:</strong> This is a public (PKCE-enabled) client, so it doesn't have a client
                secret.
            @endif
        </p>
    @else
        This page's content has expired.
    @endif

    <a href="{{ url()->previous("/nova") }}">Go back to Nova</a>

@endsection
