:og:description: RoboJackets members who pay dues are eligible to recieve merchandise, such as a t-shirt or polo. When you physically give someone merchandise, you must record it in Apiary.
.. meta::
   :keywords: swag

Merchandise
===========

.. vale write-good.E-Prime = NO

RoboJackets members who pay dues are eligible to receive merchandise, such as a t-shirt or polo.
When you physically give someone merchandise, you must record it in Apiary.

When a member pays for a dues package, they can select from the merchandise attached to the package, if any.

.. hint::
   To record distribution or view transactions, you must have an :ref:`officer`, :ref:`project-manager`, or :ref:`team-lead` role.
   If you need access, ask in :slack:`it-helpdesk`.

Confirming merchandise selections
---------------------------------

To confirm which merchandise a member selected when they paid dues, follow these steps.

#. From the Apiary homepage, click the :guilabel:`Admin` link in the top navigation bar.
#. Under the :guilabel:`Other` header in the left sidebar, click :guilabel:`Users`.
#. Search for the member using the search box under the :guilabel:`Users` header, then select the user from the results.
#. Scroll to the :guilabel:`Dues Transactions` heading, and select the transaction for which you want to distribute merchandise.
#. On the transaction detail page, scroll to the :guilabel:`Merchandise` heading.
   This section lists the merchandise selections made when the member paid dues.
#. To distribute an item, select it, then follow the steps in the next section.

Recording merchandise distribution on web
-----------------------------------------

To record distribution of an item, follow these steps.

#. Open the details page for the merchandise item you want to distribute.
   You can either use the steps in the preceding section, or look at the :guilabel:`Merchandise` list under the :guilabel:`Dues` heading in the left sidebar.
#. Click the Actions menu (three dots |actionsmenu|) to the right of the :guilabel:`Merchandise Details` header, then select the :guilabel:`Distribute Merchandise` option.
#. Search for the user you're distributing merchandise to, then click :guilabel:`Mark as Picked Up`.
   If you don't see the user's name in the list, they may not be eligible to receive the item, or they may not have selected it when they paid dues.
   You can ask in :slack:`it-helpdesk` if you're not sure.

.. hint::
   Administrators can follow this process with the :guilabel:`Undo Merchandise Distribution` action to clear distribution, if needed.

Recording merchandise distribution on Android
---------------------------------------------

.. vale Google.Parens = NO

RoboJackets developed an Android app to ease distribution of merchandise.
Your phone must support NFC and run Android 7 (Nougat) or newer.

.. vale Google.Parens = YES

To distribute merchandise using the app:

.. vale Google.Passive = NO
.. vale Google.Will = NO
.. vale write-good.E-Prime = NO
.. vale write-good.Passive = NO

#. Download the app from the `Google Play Store <https://play.google.com/store/apps/details?id=org.robojackets.apiary>`_.
#. Open the app and follow the prompts to sign in.
#. After signing in, tap :guilabel:`Merchandise`.
#. Select the specific merchandise type.
#. A new screen will appear that says :guilabel:`Tap a BuzzCard`.
   You can now hold a physical BuzzCard to the back of your phone until you feel your phone vibrate, and the screen in the app changes to :guilabel:`Processing`.
#. You can manually enter a GTID using the :guilabel:`Enter GTID manually` button if someone forgot their BuzzCard.
   If a valid BuzzCard consistently displays an error message, post in :slack:`apiary-mobile`.
#. Read the instructions on-screen.

The app uses the NFC radio in your phone to read data from BuzzCards. Below are some tips for consistent, successful reads:

.. vale write-good.Weasel = NO

- If you have a particularly thick case on your phone, try removing it.
- Search the Internet to determine the location of the NFC antenna on your phone.
  Generally, cards will read more reliably when centered on the antenna.
- Remove the card for a few seconds, then try again.

If you need help, post in :slack:`apiary-mobile`.
