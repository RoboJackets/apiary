:og:description: An access override allows someone access to (mostly electronic) RoboJackets services before they pay dues for the current semester. Typically, access overrides are provided on a case-by-case basis upon request.

Access overrides
================

.. vale Google.Parens = NO
.. vale Google.Passive = NO
.. vale Google.Will = NO
.. vale write-good.E-Prime = NO
.. vale write-good.Passive = NO
.. vale write-good.TooWordy = NO
.. vale write-good.Weasel = NO

An access override allows someone access to (mostly electronic) RoboJackets services before they pay dues for the current semester.
Typically, access overrides are provided on a case-by-case basis upon request.

Self-service
------------

.. seealso::
   There is a :doc:`separate member-facing page for self-service overrides </members/access-overrides>`.

To prevent misuse, a user must meet several criteria before they become eligible for a self-service override.
A user's eligibility for a self-service override is visible on their details page in Nova, in the System Access panel.

Self-service overrides can last between 7 and 60 days.
The longest duration will occur if the earliest future :ref:`dues package <Dues package>` :guilabel:`Access End Date` is 60 or more days in the future.
The shortest duration will occur if the earliest future :guilabel:`Access End Date` is 7 or fewer days away.

Self-service override criteria are divided into 2 categories: conditions and tasks.
A condition is a system- or user-level property that a user can't rectify alone.
A task must be completed by the user themselves.

Required conditions
~~~~~~~~~~~~~~~~~~~

- User access must not be already active
- User must have no prior dues payments
- User must have no active or prior access override
- A :ref:`dues package <Dues package>` with an :guilabel:`Access End Date` in the future must exist

Required tasks
~~~~~~~~~~~~~~

- Sign the membership agreement
- Attend a team meeting
   - Attendance must be recorded in Apiary for it to count, either via kiosk, Android app, or a remote attendance link
   - Only teams in Apiary that have the :guilabel:`Self-Service Override Eligible` property set to true in Nova count toward this requirement.
     This means that event attendance, training attendance, and Core or discipline Core attendance won't meet this requirement.

Manual grant
------------

Administrators can generally grant manual access overrides to users, even if they're not eligible for a self-service access override.

Manual overrides have two requirements:

- Administrators can't grant overrides to themselves
- The user receiving the override must have signed the latest membership agreement

.. tip::
   Administrators with access to either Nomad or the server itself may apply overrides without meeting those requirements, either with Tinker or in the database.

To manually grant an access override:

#. Search for the user and open their detail page.
#. Click the Actions menu (three dots |actionsmenu|) to the right of the :guilabel:`User Details` header, then choose the :guilabel:`Override Access` option.
#. A popup will appear to select a date for the override expiration.
   In general, this should be set to the next dues deadline.
#. Click the blue :guilabel:`Override Access` button.

This will add a card to the user's dashboard showing their override expiration date and sync their access to other systems.
Note that access sync is an asynchronous process and it may take several seconds to fully propagate changes.
