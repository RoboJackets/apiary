:og:description: Apiary supports granular access control to different functions of the software.

Permissions and roles
=====================

.. vale Google.Passive = NO
.. vale write-good.E-Prime = NO
.. vale write-good.Passive = NO
.. vale write-good.Weasel = NO

Apiary supports granular access control to limit access to the software.
Most actions require a specific permission, and permissions are grouped into roles.
Users can be assigned any combination of roles and permissions, and their effective access is cumulative across all assigned roles and permissions.

To attach or detach a role or permission, open a user's detail page, then scroll to the :guilabel:`Roles` or :guilabel:`Permissions` section.

Role descriptions
-----------------

Roles provide a quick way to assign a group of permissions to a user.

.. warning::
   For service accounts using the :doc:`/admins/api/index`, assign specific permissions rather than a role.

.. _admin:

``admin``
~~~~~~~~~
This role grants access to almost all functions and should only be assigned to system administrators, developers, and other technical users. Some functions check for an associated ``admin`` role rather than a specific permission.

.. _officer:

``officer``
~~~~~~~~~~~

- View users, :ref:`dues transactions <Dues Transaction>`, and :doc:`payments </officers/payments/index>`
- Create and update :ref:`dues packages <Dues Package>`
- Create and update :doc:`events </officers/events>`
- :doc:`Take attendance for any team or event </officers/attendance>`
- :ref:`Record cash, check, or waiver payments <Recording an Offline Payment>`
- :ref:`Distribute merchandise <Recording Merchandise Distribution>`
- Manage travel

.. _project-manager:

``project-manager``
~~~~~~~~~~~~~~~~~~~

- View users, :ref:`dues transactions <Dues Transaction>`, and :doc:`payments </officers/payments/index>`
- :doc:`Take attendance for any team or event </officers/attendance>`
- :ref:`Record cash or check payments <Recording an Offline Payment>`
- :ref:`Distribute merchandise <Recording Merchandise Distribution>`
- Manage travel

.. _team-lead:

``team-lead``
~~~~~~~~~~~~~

- View users, :ref:`dues transactions <Dues Transaction>`, and :doc:`payments </officers/payments/index>`
- :doc:`Take attendance for any team or event </officers/attendance>`
- :ref:`Distribute merchandise <Recording Merchandise Distribution>`

.. _trainer:

``trainer``
~~~~~~~~~~~

- View users
- :doc:`Take attendance for any team or event </officers/attendance>`

``member`` and ``non-member``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
These are identical roles that provide baseline access to end users. They're assigned automatically based on user behavior and shouldn't be manually attached or detached.

Restricted permissions
----------------------

The following permissions aren't included with any roles, and must be manually assigned to a user if necessary.

.. _refund-payments:

``refund-payments``
~~~~~~~~~~~~~~~~~~~

Most payments are considered non-refundable. If an exception is made, this permission should be attached to the treasurer or other financial officer. This enables the :doc:`Refund Payments action </officers/payments/refund>`.

``impersonate-users``
~~~~~~~~~~~~~~~~~~~~~

This permission enables `impersonation within Laravel Nova <https://nova.laravel.com/docs/4.0/customization/impersonation.html>`__. It should only be attached to developers while debugging an issue.

``authenticate-with-docusign``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. vale Google.Parens = NO

This permission enables a convenience URL (``/sign/auth``) to configure `DocuSign impersonation <https://developers.docusign.com/platform/auth/jwt/>`__. It should only be attached to system administrators that are configuring DocuSign.
