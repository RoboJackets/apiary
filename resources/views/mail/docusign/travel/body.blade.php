@if($travel->tar_required && $travel->needs_airfare_form)
    Please carefully review and complete the included forms as soon as possible so we can book airfare for you. Georgia Tech requires these forms for all official travel.
@elseif($travel->tar_required && ! $travel->needs_airfare_form)
    Please carefully review and initial the included form as soon as possible. Georgia Tech requires this form for all official travel.
@elseif(! $travel->tar_required && $travel->needs_airfare_form)
    Please carefully review and complete the included form as soon as possible so we can book airfare for you. Georgia Tech requires this form for all official travel.
@endif
