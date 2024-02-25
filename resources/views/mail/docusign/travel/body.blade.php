@if($travel->needs_travel_information_form && $travel->needs_airfare_form)
    Please carefully review and complete the included forms as soon as possible so we can book airfare for you. Georgia Tech requires these forms for all official travel.
@elseif($travel->needs_travel_information_form && ! $travel->needs_airfare_form)
    Please carefully review and initial the included form as soon as possible so we can book travel arrangements for you. Georgia Tech requires this form for all official travel.
@elseif(! $travel->needs_travel_information_form && $travel->needs_airfare_form)
    Please carefully review and complete the included form as soon as possible so we can book airfare for you. Georgia Tech requires this form for all official travel.
@endif
