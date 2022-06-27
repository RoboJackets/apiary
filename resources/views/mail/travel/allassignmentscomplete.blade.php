Hi {{ $travel->primaryContact->preferred_first_name }},

All travelers have paid the travel fee{{ $travel->tar_required ? ' and submitted Travel Authority Requests' : '' }} for {{ $travel->name }}. Contact the treasurer to book travel.
@if($travel->tar_required)

You can download all submitted forms from {{ config('app.name') }} at {{ route('nova.pages.detail', ['resource' => 'travel', 'resourceId' => $travel->id]) }}, under the action menu (three dots in top right).
@endif

Bon voyage!

----

To stop receiving emails from {{ config('app.name') }}, visit @{{{ pm:unsubscribe }}}.
