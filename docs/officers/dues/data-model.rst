Data Model
==========

Dues are tracked in Apiary using several related objects.

-----------
Fiscal Year
-----------

A **Fiscal Year** groups together several `Dues Package`_ and `Merchandise`_ objects.
Fiscal years are primarily used for reporting and do not affect any functionality.
They need to be created manually once a year to group the objects for that year.

---------------
_`Dues Package`
---------------

A **Dues Package** represents an option for paying dues.
In the member-facing interface, packages are labeled as "Dues Terms."
There are a few key attributes:

- The **cost** of the package
- The **membership start date**, when someone is considered to be an active member if they purchase this package
- The **membership end date**, when someone is no longer considered to be an active member unless/until they purchase another package
- The **access start date**, when someone will gain access to RoboJackets systems if they purchase this package
- The **access end date**, when someone will lose access to RoboJackets systems unless/until they purchase another package

Additional attributes are used to determine whether a package may be offered to a specific person, based on their affiliation with Georgia Tech and prior purchase history.

A **Dues Package** may also have links to one or more `Merchandise`_ objects, which may be selected when choosing that package.

--------------
_`Merchandise`
--------------

A **Merchandise** object represents a branded item that is included with a `Dues Package`_.
This typically includes t-shirts and polos, but other options may be available at the discretion of the officer team.

-------------------
_`Dues Transaction`
-------------------

A **Dues Transaction** represents a request from a member to purchase a specific `Dues Package`_.
It may include `Merchandise`_ selections, if there are options associated with the package.

A transaction may have one or more :doc:`../payments` associated.

.. attention::
   Transactions should not be created manually by officers -- they are created automatically through the member-facing interface when a member begins the dues workflow.
