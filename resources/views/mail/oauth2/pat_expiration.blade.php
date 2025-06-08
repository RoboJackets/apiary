Hi {!! $token->user->preferred_first_name !!},

@if($already_expired)
Your {{ config('app.name') }} personal access token called "{{ $token->name }}" expired on {{ $token->expires_at }} and is no longer valid.
@else
Your {{ config('app.name') }} personal access token called "{{ $token->name }}" will expire on {{ $token->expires_at }} and will not work after that time.
@endif

You can view this token at {{ route('nova.pages.detail', ['resource' => \App\Nova\OAuth2AccessToken::uriKey(), 'resourceId' => $token->id]) }}.

Token expiration dates cannot be extended. If you're still using this token, you must create a new token to continue accessing the {{ config('app.name') }} API. You can create a new token by visiting your user page at {{ route('nova.pages.detail', ['resource' => \App\Nova\User::uriKey(), 'resourceId' => $token->user_id]) }} and running the "Create Personal Access Token" action.

If you need any assistance, ask in #it-helpdesk in Slack.

----

To stop receiving emails from {{ config('app.name') }}, visit @{{{ pm:unsubscribe }}}.
