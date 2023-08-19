:og:description: Apiary supports collecting RSVPs and attendance for one-off events.

Events
======

.. vale Google.Parens = NO
.. vale Vale.Spelling = NO

Apiary supports collecting :abbr:`RSVPs (Répondez s'il vous plaît)` and attendance for one-off events.

.. vale Google.Parens = YES
.. vale Vale.Spelling = YES

Creating an event
-----------------

.. vale Google.Acronyms = NO
.. vale write-good.Passive = NO
.. vale write-good.E-Prime = NO
.. vale Google.Passive = NO

.. hint::
   To create an event, you must have the :ref:`officer` role.
   If you need access, ask in :slack:`it-helpdesk`.

#. From the Apiary homepage, click the :guilabel:`Admin` link in the top navigation bar.
#. Under the :guilabel:`Meetings` header in the left sidebar, click :guilabel:`Events`.
#. In the top-right corner, click :guilabel:`Create Event`.
#. Enter the event name and organizer.
   Optionally enter start and end times and a location for the event.
   Note that the event name, start time, and location are shown to people when they RSVP.
#. If you want to allow people to RSVP to the event without logging in to Apiary, select :guilabel:`Allow Anonymous RSVPs`.
   This may increase the response rate at the risk of increasing duplicate responses.

.. vale Google.Headings = NO

Collecting RSVPs
----------------

After creating the event, the details page shows an :guilabel:`RSVP URL`.
You can send this link to anyone to allow them to RSVP to the event.

You can also append a ``source`` query parameter to the URL to track clicks from different channels, such as newsletters or the website.
For example, you can append ``?source=website`` to the end of the URL.

Note that if you turned off :guilabel:`Allow Anonymous RSVPs`, people must log in to record their RSVP.

Collecting attendance
---------------------

Event attendance works the same way as for teams.
See :doc:`/officers/attendance` for more information.
