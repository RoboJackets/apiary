Hi {!! $envelope->signedBy->preferred_first_name !!},

We processed your signed membership agreement. You can view the full text of the agreement within DocuSign at {{ $envelope->sender_view_url }}, or attached to a separate email from DocuSign.

If you have any questions, just reply to this email.

----

To stop receiving emails from {{ config('app.name') }}, visit @{{{ pm:unsubscribe }}}.
