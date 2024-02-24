:og:description: Apiary's trip management features ensure accurate and consistent booking processes for trips.

Set up a trip
=============

Apiary's trip management features ensure accurate and consistent booking processes for trips.

.. hint::
   To use these features, you must have an :ref:`officer` or :ref:`project-manager` role.
   If you need access, ask in :slack:`it-helpdesk`.

Create a trip
-------------

.. vale Google.WordList = NO

#. From the Apiary homepage, click the :guilabel:`Admin` link in the top navigation bar.
#. Under the :guilabel:`Travel` header in the left sidebar, click :guilabel:`Trips`.
#. Click the blue :guilabel:`Create Trip` button above the top-right of the list.
#. Fill out the form.
#. Click the blue :guilabel:`Create Trip` button below the bottom-right of the form.

.. vale write-good.E-Prime = NO

Your newly created trip is in :guilabel:`Draft` status, so you can update the trip and any associated trip assignments until it's approved by an officer.

Configure airfare policy
~~~~~~~~~~~~~~~~~~~~~~~~

.. hint::
   To configure the airfare policy, you must have the :ref:`update-airfare-policy` permission, which isn't included with any roles.
   If you need access, ask in :slack:`it-helpdesk`.

.. vale Google.Passive = NO
.. vale write-good.Passive = NO

If you configured the trip to collect an :guilabel:`Airfare Request Form`, an :guilabel:`Airfare Policy` is required.
This defines what itineraries may be added to assignments for this trip.

.. vale Google.Will = NO

If an itinerary doesn't meet the configured policy, an error message will display the specific rule that was triggered.

Create trip assignments
-----------------------

#. On the :guilabel:`Trip Details` page, scroll to the :guilabel:`Assignments` section, then click the blue :guilabel:`Create Trip Assignment` button above the top-right of the list.
#. Select the member to assign to the trip. The member can't be changed after the assignment is created, but the assignment can be deleted while the trip is in :guilabel:`Draft` status.
#. If your trip is configured to collect an :guilabel:`Airfare Request Form`, provide a flight itinerary in the :guilabel:`Matrix Itinerary` field in :doc:`Matrix JSON format </officers/travel/matrix>`.
   Note that you need to remove the ``null`` value that's in the field by default.

Approve a trip
--------------

.. hint::
   To approve trips, you must have the :ref:`officer` role.
   If you need access, ask in :slack:`it-helpdesk`.

Once all assignments have been created, the trip must be approved by an officer. This ensures that all trip details are accurate before travelers are notified of their assignments.

#. On the :guilabel:`Trip Details` page, click the Actions menu (three dots |actionsmenu|) to the right of the :guilabel:`Trip Details` header.
#. Select :guilabel:`Review Trip`.
#. A popup will appear with a summary of the trip details and review criteria.
   Read through the popup and select the checkbox for each review criterion.

   #. If any changes need to be made to the trip, click :guilabel:`Cancel` at the bottom of the popup, and work with the primary contact to make the changes.
   #. If all criteria are met, click :guilabel:`Approve` at the bottom of the popup.
#. Once the trip is approved, travelers will receive email notifications with instructions to pay and submit forms, if needed.

Collect payments
----------------

Travelers can pay the trip fee online the same way they pay dues. For more information, see :doc:`/officers/payments/accept`.

Collect forms
-------------

Travelers with complete profiles in Apiary may receive their forms via DocuSign as soon as the trip is approved.
Some travelers may need to visit Apiary first for more specific instructions.

The primary contact's DocuSign account is used to send forms.
DocuSign will send an email copy of all completed forms to the primary contact by default.
This can be configured within DocuSign if desired.

Correct a form
~~~~~~~~~~~~~~

If a form needs to be corrected, and hasn't been completed yet, the primary contact for the trip can use the :guilabel:`Void Envelope` action on the envelope within Apiary, or the :guilabel:`Void` option within DocuSign directly.

If the envelope was already completed, use the :guilabel:`Delete Resource` action on the envelope within Apiary.

In either case, the traveler will need to restart the signing process within Apiary.

.. vale Google.Headings = NO
.. vale Google.Acronyms = NO
.. vale Google.Parens = NO

Download :abbr:`IAA (Institute Approved Absence)` request
---------------------------------------------------------

After a trip is approved, the primary contact can download a :abbr:`CSV (comma-separated values)` export to request Institute Approved Absences for all travelers.
Each traveler must have emergency contact information on their Apiary profile.

#. On the :guilabel:`Trip Details` page, click the Actions menu (three dots |actionsmenu|) to the right of the :guilabel:`Trip Details` header.
#. Select :guilabel:`Download IAA Request`.

Review the downloaded CSV for accuracy and completeness before forwarding it to Georgia Tech.

Download forms
--------------

After all travelers have submitted forms, the primary contact can download a ZIP file with all forms.

#. On the :guilabel:`Trip Details` page, click the Actions menu (three dots |actionsmenu|) to the right of the :guilabel:`Trip Details` header.
#. Select :guilabel:`Download Forms`.

Review all forms for accuracy and completeness before forwarding them to Georgia Tech.
