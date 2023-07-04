:og:description: Dues are tracked in Apiary using several related objects.

Data Model
==========

Dues are tracked in Apiary using several related objects.

Fiscal Year
-----------

A **Fiscal Year** groups together several :ref:`Dues Package` and :ref:`Merchandise` objects.
Fiscal years are primarily used for reporting and do not affect any functionality.
They need to be created manually once a year to group the objects for that year.

Dues Package
------------

A :guilabel:`Dues Package` represents an option for paying dues.
In the member-facing interface, packages are labeled as :guilabel:`Dues Terms`.
There are a few key attributes:

- The **cost** of the package
- The **membership start date**, when someone is considered to be an active member if they purchase this package.
  Generally, this is August 1st for fall and full year dues, and January 1st for spring dues.
- The **membership end date**, when someone is no longer considered to be an active member unless/until they purchase another package.
  Generally, this is August 1st for spring or full year dues, and January 1st for fall dues.
- The **access start date**, when someone will gain access to RoboJackets systems if they purchase this package.
  Generally, this matches the membership start date.
- The **access end date**, when someone will lose access to RoboJackets systems unless/until they purchase another package.
  Generally, this is the dues deadline for the following semester.

Additional attributes are used to determine whether a package may be offered to a specific person, based on their affiliation with Georgia Tech and prior purchase history.

A :guilabel:`Dues Package` may also have links to one or more :ref:`Merchandise` objects, which may be selected by members when choosing that package.

.. attention::
   Packages generally should not be created manually by officers; there is an :ref:`action on Fiscal Years<Create Dues Packages>` that sets up default packages.

Merchandise
-----------

A :guilabel:`Merchandise` object represents a branded item that is included with a :ref:`Dues Package`.
This typically includes t-shirts and polos, but other options may be available at the discretion of the officer team.

.. important::
   Merchandise should be fully configured for a dues package **before** making it available for purchase.
   If new options are added later, prior transactions cannot be updated by members directly, and must be updated by an admin.

Dues Transaction
----------------

A :guilabel:`Dues Transaction` represents a request from a member to purchase a specific :ref:`Dues Package`.
It may include :ref:`Merchandise` selections, if there are options associated with the package.

A transaction may have one or more payments associated.

.. attention::
   Transactions should **not** be created manually by officers; they are created automatically through the member-facing interface when a member begins the dues workflow.
