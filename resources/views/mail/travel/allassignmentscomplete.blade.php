Hi {!! $travel->primaryContact->preferred_first_name !!},
{{-- all assignments paid and documented --}}
@if(! $travel->assignments_need_payment && ! $travel->assignments_need_forms)
@if($travel->fee_amount > 0)
All travelers have paid the trip fee{{ $travel->needs_docusign ? ' and submitted forms' : '' }} for {{ $travel->name }}.{{ $travel->departure_date > \Carbon\Carbon::now() ? ' Contact the treasurer to book travel arrangements.' : '' }}
@else
All travelers have submitted forms for {{ $travel->name }}.{{ $travel->departure_date > \Carbon\Carbon::now() ? ' Contact the treasurer to book travel arrangements.' : '' }}
@endif
@if($travel->needs_docusign)

You can download all submitted forms from {{ config('app.name') }} at {{ route('nova.pages.detail', ['resource' => \App\Nova\Travel::uriKey(), 'resourceId' => $travel->id]) }}, under the action menu (three dots in top right).
@endif
@if($travel->departure_date > \Carbon\Carbon::now())

Bon voyage!
@endif
{{-- all assignments documented, not paid--}}
@elseif($travel->assignments_need_payment && ! $travel->assignments_need_forms)
All travelers have submitted forms for {{ $travel->name }}. @if($travel->assignments()->unpaid()->count() === 1)
1 traveler still needs to pay the trip fee.
@else{{ $travel->assignments()->unpaid()->count() }} travelers still need to pay the trip fee.
@endif

You can download all submitted forms from {{ config('app.name') }} at {{ route('nova.pages.detail', ['resource' => \App\Nova\Travel::uriKey(), 'resourceId' => $travel->id]) }}, under the action menu (three dots in top right).
{{-- all assignments paid, not documented --}}
@elseif(! $travel->assignments_need_payment && $travel->assignments_need_forms)
All travelers have paid the trip fee for {{ $travel->name }}. @if($travel->assignments()->needDocuSign()->count() === 1)
1 traveler still needs to submit {{ $travel->needs_airfare_form ? ($travel->needs_travel_information_form ? 'forms' : 'an airfare request form') : ($travel->needs_travel_information_form ? 'a travel information form' : '') }}.
@else{{ $travel->assignments()->needDocuSign()->count() }} travelers still need to submit forms.
@endif

@endif
----

To stop receiving emails from {{ config('app.name') }}, visit @{{{ pm:unsubscribe }}}.
