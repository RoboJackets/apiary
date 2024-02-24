:og:description: Apiary models a trip as a single event with many travelers. A trip assignment links each traveler to a trip.

Data model
==========

.. vale write-good.Weasel = NO

Apiary models a **trip** as a single event with many travelers. A **trip assignment** links each traveler to a trip.

Trip
----

A **trip** has a single destination and many travelers.

Trips have a few key attributes:

.. vale write-good.E-Prime = NO

- The **status** determines whether a trip is visible to travelers
- The **primary contact** person responsible for organizing the trip
- The **trip fee amount** to collect from each traveler
- The **forms to collect** via DocuSign

.. vale write-good.TooWordy = NO

Depending on the forms you're collecting, you'll need to provide additional information about the trip.

Trip status
~~~~~~~~~~~

A trip can be in one of three statuses:

.. vale Google.Passive = NO
.. vale write-good.Passive = NO

- :guilabel:`Draft` trips can be updated by the primary contact or anyone with access to manage trips.
   - Assignments can be created, updated, or deleted as needed.
   - Assignments aren't visible to travelers, can't be paid, and forms can't be sent.
- :guilabel:`Approved` trips are locked and can no longer be modified.
   - Assignments are also locked and can't be created, updated, or deleted.
   - Assignments are visible to travelers, email notifications are sent, and travelers can pay the trip fee and submit forms.
- :guilabel:`Complete` trips are locked and can no longer be modified.

A trip always start in :guilabel:`Draft` status.

When an officer approves a trip, it moves to :guilabel:`Approved` status. **Approving a trip is final -- trips can't be moved back to** :guilabel:`Draft` **status after approval**.

Once all payments and forms are received, the trip moves to :guilabel:`Complete` status.

Trip assignment
---------------

A **trip assignment** links a specific traveler to a specific trip.

If the trip includes air travel, you'll need to provide a flight itinerary for each assignment in :doc:`Matrix JSON format </officers/travel/matrix>`.
Assignments can have different itineraries if needed.

An assignment may have zero or more :doc:`payments </officers/payments/index>` or :ref:`DocuSign envelopes <DocuSign envelope>` associated.

.. vale Google.Headings = NO

DocuSign envelope
-----------------

A single **DocuSign envelope** contains all forms for a specific traveler.

.. vale Google.Will = NO

Apiary will automatically collect and index submitted forms so that they can be downloaded as a single ZIP file.
