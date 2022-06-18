Hi {{ $travel->primaryContact->preferred_first_name }},

All travelers have paid the travel fee{{ $travel->tar_required ? ' and submitted Travel Authority Requests' : '' }} for {{ $travel->name }}. Contact the treasurer to book travel.

Bon voyage!

----

To stop receiving emails from {{ config('app.name') }}, visit @{{{ pm:unsubscribe }}}.
