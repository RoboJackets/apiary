@if($user->docusign_refresh_token_expires_at === null)
    <div class="flex items-center space-x-2 font-bold text-sky-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="text-sm">
                Never linked
        </span>
    </div>
@elseif($user->docusign_refresh_token_expires_at < \Carbon\Carbon::now())
    @if($user->id === request()->user()->id)
        <div class="flex items-center space-x-2 font-bold text-red-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span class="text-sm">
                DocuSign credentials expired on {{ $user->docusign_refresh_token_expires_at->format('l, F j, Y') }} - <a class="text-sky-500" href="{{ route('docusign.auth.deeplink', ['resource' => \App\Nova\User::uriKey(), 'resourceId' => $user->id]) }}">click here</a> to link your DocuSign account again
            </span>
        </div>
    @else
        <div class="flex items-center space-x-2 font-bold text-red-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span class="text-sm">
                DocuSign credentials expired on {{ $user->docusign_refresh_token_expires_at->format('l, F j, Y') }}
            </span>
        </div>
    @endif
@else
    <div class="flex items-center space-x-2 font-bold text-green-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="text-sm">
            DocuSign credentials are valid until {{ $user->docusign_refresh_token_expires_at->format('l, F j, Y') }}
        </span>
    </div>
@endif
