Hi {{ $signature->user->preferred_first_name }},

@if($signature->electronic)
We received your electronically signed membership agreement at {{ $signature->cas_ticket_redeemed_timestamp }} from IP address {{ $signature->ip_address }}.
@else
Your signed membership agreement was uploaded to {{ config('app.name') }} at {{ $signature->updated_at }} by {{ $signature->uploadedBy->name }}.
@endif

The full text of the agreement is included below for your reference.

---

{{ $agreement_text }}

---

If you have any questions, just reply to this email.

----

To stop receiving emails from {{ config('app.name') }}, visit @{{{ pm:unsubscribe }}}.
