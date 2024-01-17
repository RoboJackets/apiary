@if($travel->tar_required && $travel->needs_airfare_form)
    {{ $travel->name }} Travel Forms
@elseif($travel->tar_required && ! $travel->needs_airfare_form)
    {{ $travel->name }} Travel Information Form
@elseif(! $travel->tar_required && $travel->needs_airfare_form)
    {{ $travel->name }} Airfare Request Form
@endif
