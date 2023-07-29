:og:description: An access override allows someone access to (mostly electronic) RoboJackets services before they pay dues for the current semester. Typically, access overrides are provided on a case-by-case basis upon request.

Access Override
===============
An access override allows someone access to (mostly electronic) RoboJackets services before they pay dues for the current semester.
Typically, access overrides are provided on a case-by-case basis upon request.

Self-Service
------------

.. seealso::
   There is a :doc:`separate member-facing page for self-service overrides</members/access-override>`.

To prevent abuse, several criteria must be met before a user becomes eligible for a self-service override.
Under normal circumstances, it should be very easy for a well-intentioned RoboJackets new member to receive a self-service override.

A user's eligibility for a self-service override is visible on their details page in Nova, in the System Access panel.

Self-service overrides can last between 7 and 60 days.
The longest duration will occur if the next (earliest) future :ref:`Dues Package` :guilabel:`Access End Date` is 60 or more days in the future.
The shortest duration will occur if the next future :guilabel:`Access End Date` is 7 or fewer days away.

Self-service override criteria are divided into 2 categories: conditions and tasks.
A condition is a system- or user-level property that cannot be rectified by the user alone (see list below). However, tasks can be completed by users.

Required Conditions
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

- User access must not be active
- User must have no prior dues payments
- User must have no active or prior access override
- A :ref:`Dues Package` with the :guilabel:`Access End Date` in the future must exist

Required Tasks
~~~~~~~~~~~~~~

- Sign the membership agreement
- Attend a team meeting
   - Attendance must be recorded in MyRoboJackets for it to count.
   - Only teams in MyRoboJackets that have the :guilabel:`Self-Service Override Eligible` property set to true in Nova will count towards this requirement.
     This means that event attendance (e.g., General Interest meetings), training attendance, and Core or discipline Core attendance will not satisfy this requirement.

Manual Grant
------------

Users can generally be granted manual access overrides by administrators, even if they are not eligible for a self-service access override.

There are two requirements:

- Administrators cannot grant overrides to themselves
- The user receiving the override must have signed the latest membership agreement

.. tip::
   Administrators with access to either Nomad or the production server itself may apply overrides without meeting those requirements, either with Tinker or in the database.

To manually grant an access override:

#. Search for the user and open their detail page.
#. Click the Actions menu (three dots |actionsmenu|) to the right of the :guilabel:`User Details` header, then choose the :guilabel:`Override Access` option.
#. A popup will appear to select a date for the override expiration.
   In general, this should be set to the next dues deadline.
#. Click the blue :guilabel:`Override Access` button.

This will add a card to the user's dashboard showing their override expiration date and sync their access to other systems.
Note that access sync is an asynchronous process and it may take several seconds to fully propagate changes.
