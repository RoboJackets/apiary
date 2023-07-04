:og:description: An access override allows someone access to (mostly electronic) RoboJackets services before they pay dues for the current semester. Typically, access overrides are provided on a case-by-case basis upon request.

Access Override
===============
An access override allows someone access to (mostly electronic) RoboJackets services before they pay dues for the current semester.
Typically, access overrides are provided on a case-by-case basis upon request.

Self-Service
------------

.. seealso::
   There is a :doc:`separate member-facing page for self-service overrides<../members/access-override>`.

To prevent abuse, several criteria must be met before a user becomes eligible for a self-service override.
Under normal circumstances, it should be very easy for a well-intentioned RoboJackets new member to receive a self-service override.

A user's eligibility for a self-service override is visible on their details page in Nova, in the System Access panel.

Self-service overrides can last between 7 and 60 days.
The longest duration will occur if the next (earliest) future :doc:`dues package<../officers/dues/data-model>` access end date is 60 or more days in the future.
The shortest duration will occur if the next future access end date is 7 or fewer days away.

Self-service override criteria are divided into 2 categories: conditions and tasks.
A condition is a system- or user-level property that cannot be rectified by the user alone (see list below). However, tasks can be completed by users.

Required Conditions
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

- User access must not be active
- User must have no prior dues payments
- User must have no active or prior access override
- A :doc:`dues package<../officers/dues/data-model>` with the "access end" date in the future must exist

Required Tasks
~~~~~~~~~~~~~~

- Sign the membership agreement
- Attend a team meeting
   - Attendance must be recorded in MyRoboJackets for it to count.
   - Only teams in MyRoboJackets that have the Self-Service Override Eligible property set to true in Nova will count towards this requirement.
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

.. raw:: html

    <ol class="arabic simple">
        <li>
            <p>
                Search for the user and open their detail page.
            </p>
        </li>
        <li>
            <p>
                Click the Actions menu (three dots <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -5 20 20" fill="currentColor" width="20" height="20" class="inline" role="presentation"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 0 14 0zM16 12a2 2 0 100-4 2 2 0 000 4z"></path></svg>) to the right of the <strong>User Details</strong> header, then choose the <strong>Override Access</strong> option.
            </p>
        </li>
        <li>
            <p>
                A popup will appear to select a date for the override expiration. In general, this should be set to the next dues deadline.
            </p>
        </li>
        <li>
            <p>
                Click the blue <strong>Override Access</strong> button.
            </p>
        </li>
    </ol>

This will add a card to the user's dashboard showing their override expiration date and sync their access to other systems.
Note that access sync is an asynchronous process and it may take several seconds to fully propagate changes.
