@extends('layouts/app')

@section('title')
   Teams | {{ config('app.name') }}
@endsection

@section('content')
    @component('layouts/title')
        Teams
    @endcomponent
    <div class="row">
        @foreach ($teams as $team)
            <div class="col-sm-12 col-md-6 col-lg-4 card-teams">
                <div class="card mb-3 h-100">
                    <div class="card-body" style="padding-bottom: 0">
                        <h5 class="card-title"><b>{{$team->name}}</b></h5>
                        <p class="card-text">{{ $team->description }}</p>
                        @if($team->mailing_list_name || $team->slack_channel_id)
                            <hr/>
                            <p class="card-text">
                                @if($team->mailing_list_name)
                                    Mailing List: <a href="https://lists.gatech.edu/sympa/info/{{$team->mailing_list_name}}" target="_blank">{{$team->mailing_list_name}}</a>
                                    <br/>
                                @endif
                                @if($team->slack_channel_id)
                                    Slack: <a href="http://robojackets.slack.com/messages/{{$team->slack_channel_id}}">#{{$team->slack_channel_name}}</a>
                                @endif
                            </p>
                        @endif
                    </div>
                    <div class="card-footer" style="min-height: 63px">
                        <team-membership-button :team="{{ $team }}" :user="{{ $user }}"></team-membership-button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
