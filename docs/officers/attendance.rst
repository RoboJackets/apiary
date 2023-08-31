:og:description: Apiary supports recording attendance for both in-person and online meetings, using either BuzzCards or links.

Attendance
==========

Apiary supports recording attendance for both in-person and online meetings, using either BuzzCards or links.

.. hint::
   To use either method, you must have an :ref:`officer`, :ref:`project-manager`, :ref:`team-lead`, or :ref:`trainer` role.
   If you need access, ask in :slack:`it-helpdesk`.

In-person meetings
------------------

.. note::
   For in-person meetings in the Student Competition Center, use the attendance kiosk to record attendance.

.. vale Google.Parens = NO

RoboJackets developed an Android app to read physical BuzzCards using :abbr:`NFC (near-field communication)` and upload the data to the Apiary server.
Your phone must support NFC and run Android 7 (Nougat) or newer.

.. vale Google.Parens = YES

To record attendance using the app:

.. vale Google.Passive = NO
.. vale Google.Will = NO
.. vale write-good.E-Prime = NO
.. vale write-good.Passive = NO

#. Download the app from the `Google Play Store <https://play.google.com/store/apps/details?id=org.robojackets.apiary>`_.
#. Open the app and follow the prompts to sign in.
#. After signing in, select whether you want to take attendance for a :guilabel:`Team` or an :guilabel:`Event`.
   Note that training sessions are tracked as teams.
#. Select the specific team or event.
#. A new screen will appear that says :guilabel:`Tap a BuzzCard`.
   You can now hold a physical BuzzCard to the back of your phone until you feel your phone vibrate, and the screen in the app changes to :guilabel:`Processing`.
#. You can manually enter a GTID using the :guilabel:`Enter GTID manually` button if someone forgot their BuzzCard.
   If a valid BuzzCard consistently displays an error message, please post in :slack:`apiary-mobile`.

The app uses the NFC radio in your phone to read data from BuzzCards. Below are some tips for consistent, successful reads:

.. vale write-good.Weasel = NO

- If you have a particularly thick case on your phone, try removing it.
- Search the Internet to determine the location of the NFC antenna on your phone.
  Generally, cards will read more reliably when centered on the antenna.
- Remove the card for a few seconds, then try again.

If you need help, please post in :slack:`apiary-mobile`.

Online meetings
---------------

.. important::
   Don't use attendance links for in-person meetings, as it's likely to be less accurate.
   If you have no other way to take attendance at an in-person meeting, distribute the link to the people in the room only and don't share it via Slack.

Apiary can create unique, time-limited links to record attendance for online meetings. If you provide a link to a video call when creating the link, Apiary will redirect users to the call after they click the link and log in.

To create a new link:

#. From the Apiary homepage, click the :guilabel:`Admin` link in the top navigation bar.
#. Under the :guilabel:`Meetings` header in the left sidebar, click :guilabel:`Teams` or :guilabel:`Events`.
#. Select the team or event for which you want to create a link.
#. Click the Actions menu (three dots |actionsmenu|) to the right of the :guilabel:`Team Details` or :guilabel:`Event Details` header, then choose the :guilabel:`Create Remote Attendance Link` option.
   A popup will appear.
#. If you have a Google Meet, Zoom, or Microsoft Teams video call link, you can paste it into the :guilabel:`Redirect URL` field.
#. Select an appropriate purpose from the :guilabel:`Purpose` dropdown.
#. Click the blue :guilabel:`Create Link` button.
#. You'll be redirected to a new page with your remote attendance link.
   If you provided a video call link, copy the :guilabel:`Auto-redirecting Link`.
#. Share the generated link with your meeting attendees.
   If you provided a video call link when creating the remote attendance link, **don't** share the video call link separately.

Note that links expire after 4 hours by default.
