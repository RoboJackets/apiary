@if($trip->assignments()->count() === 0)
    <small>You can add assignments and make other changes to this trip while it's in draft status.</small>
@else
    @if($trip->needs_docusign)
        @if($trip->fee_amount > 0)
            <small>When you're ready to request forms and payments from travelers, ask an officer to approve this trip.</small>
        @else
            <small>When you're ready to request forms from travelers, ask an officer to approve this trip.</small>
        @endif
    @else
        @if($trip->fee_amount > 0)
            <small>When you're ready to request payments from travelers, ask an officer to approve this trip.</small>
        @endif
    @endif
@endif
