@if($trip->primaryContact->docusign_refresh_token_expires_at < \Carbon\Carbon::now())
    @if($trip->primaryContact->id === request()->user()->id)
        <div class="flex items-center space-x-2 font-bold text-red-500" style="margin-top: 0.25rem">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span class="text-sm">
                Your DocuSign credentials expired, so forms can't be sent right now. <a class="text-sky-500" href="{{ route('docusign.auth.deeplink', ['resource' => \App\Nova\Travel::uriKey(), 'resourceId' => $trip->id]) }}">Click here</a> to link your DocuSign account again.
            </span>
        </div>
    @else
        <div class="flex items-center space-x-2 font-bold text-red-500" style="margin-top: 0.25rem">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span class="text-sm">
                {{ $trip->primaryContact->preferred_first_name }}'s DocuSign credentials expired, so forms can't be sent right now. Ask them to check this page for further instructions.
            </span>
        </div>
    @endif
@else
    <div class="flex items-center space-x-2 font-bold text-green-500" style="margin-top: 0.25rem">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="text-sm">
            DocuSign credentials are valid for sending forms until {{ $trip->primaryContact->docusign_refresh_token_expires_at->format('l, F j, Y') }}
        </span>
    </div>
@endif
