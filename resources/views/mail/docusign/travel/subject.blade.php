@if($travel->needs_travel_information_form && $travel->needs_airfare_form)
    {{ $travel->name }} Travel Forms
@elseif($travel->needs_travel_information_form && ! $travel->needs_airfare_form)
    {{ $travel->name }} Travel Information Form
@elseif(! $travel->needs_travel_information_form && $travel->needs_airfare_form)
    {{ $travel->name }} Airfare Request Form
@endif
