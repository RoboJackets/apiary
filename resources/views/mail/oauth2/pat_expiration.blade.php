@component('mail::message')
    @if($already_expired)
        Your MyRoboJackets personal access token called {{ $token->name }} expired on {{ $token->expires_at }} and is no longer valid.
    @else
        Your MyRoboJackets personal access token called {{ $token->name }} will expire on {{ $token->expires_at }} and will not work after that time.
    @endif

Personal access token expiration dates cannot be extended. If you're still using this token, you must create a new personal access token to continue accessing the MyRoboJackets API.  You can create a new personal access token by accessing your user page in Nova and running the Create Personal Access Token action.

Feel free to reach out to [#it-helpdesk](https://robojackets.slack.com/app_redirect?channel=it-helpdesk) in Slack for assistance creating a new personal access token.

-RoboJackets IT
@endcomponent
