Hi {!! $travel->primaryContact->preferred_first_name !!},
{{-- all assignments paid and documented --}}
@if(! $travel->assignments_need_payment && ! $travel->assignments_need_forms)
All travelers have paid the travel fee{{ $travel->needs_docusign ? ' and submitted travel forms' : '' }} for {{ $travel->name }}. Contact the treasurer to book travel.
@if($travel->needs_docusign)

You can download all submitted forms from {{ config('app.name') }} at {{ route('nova.pages.detail', ['resource' => \App\Nova\Travel::uriKey(), 'resourceId' => $travel->id]) }}, under the action menu (three dots in top right).
@endif

Bon voyage!
{{-- all assignments documented, not paid--}}
@elseif($travel->assignments_need_payment && ! $travel->assignments_need_forms)
All travelers have submitted travel forms for {{ $travel->name }}. @if($travel->assignments()->unpaid()->count() === 1)
1 traveler still needs to pay the travel fee.
@else{{ $travel->assignments()->unpaid()->count() }} travelers still need to pay the travel fee.
@endif

You can download all submitted forms from {{ config('app.name') }} at {{ route('nova.pages.detail', ['resource' => \App\Nova\Travel::uriKey(), 'resourceId' => $travel->id]) }}, under the action menu (three dots in top right).
{{-- all assignments paid, not documented --}}
@elseif(! $travel->assignments_need_payment && $travel->assignments_need_forms)
All travelers have paid the travel fee for {{ $travel->name }}. @if($travel->assignments()->needDocuSign()->count() === 1)
1 traveler still needs to submit forms.
@else{{ $travel->assignments()->needDocuSign()->count() }} travelers still need to submit forms.
@endif

@endif
----

To stop receiving emails from {{ config('app.name') }}, visit @{{{ pm:unsubscribe }}}.
