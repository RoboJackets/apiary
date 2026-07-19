:og:description: Admins can charge off unpaid trip fees that are unlikely to be collected, once the trip has returned.

Charging off trip fees
======================

.. vale Google.Passive = NO
.. vale write-good.E-Prime = NO
.. vale write-good.Passive = NO
.. vale write-good.Weasel = NO

Admins can charge off unpaid trip fees that are unlikely to be collected.

A charge-off isn't a payment or a waiver.
The member still owes the fee, but Apiary stops sending payment reminder emails, and stops counting the fee toward trip payment tracking on the admin homepage.

.. hint::
   To charge off trip fees, you must have the :ref:`admin` role.

A trip fee can only be charged off when all of the following are true:

- The trip's return date has passed.
- The trip isn't in draft status.
- The :ref:`trip assignment <Trip assignment>` isn't paid in full.
- The trip assignment hasn't already been charged off.

What happens after a charge-off
-------------------------------

- The member still sees the fee as an outstanding payment on their dashboard and Travel tab, and they can still pay it online or in person.
  If they pay, the assignment counts as paid, and the charge-off no longer has any effect.
- Apiary stops sending payment reminder emails for the trip assignment.
- The :guilabel:`Payment Status` chart for the trip shows charged-off assignments in a separate :guilabel:`Charged Off` segment, between :guilabel:`Paid` and :guilabel:`Not Paid`.
- Once every assignment for a past trip is either paid or charged off, the charts for the trip no longer appear on the admin homepage.

Charging off a trip fee
-----------------------

#. From the Apiary homepage, click the :guilabel:`Admin` link in the top navigation bar.
#. Under the :guilabel:`Other` header in the left sidebar, click :guilabel:`Users`.
#. Search for the member using the search box under the :guilabel:`Users` header, then select the user from the results.
#. Scroll to the :guilabel:`Trip Assignments` heading.
#. Select the trip assignment from the list.
#. Click the Actions menu (three dots |actionsmenu|) to the right of the :guilabel:`Trip Assignment Details` header.
#. Select :guilabel:`Charge Off` from the menu.
#. Click the red :guilabel:`Charge Off` button to confirm.
