:og:description: Apiary tracks dues using several interrelated objects.

Data model
==========

.. vale write-good.Weasel = NO

Apiary tracks dues using several interrelated objects.

Fiscal year
-----------

A **fiscal year** groups together several :ref:`dues package <Dues package>` and :ref:`merchandise <Merchandise>` objects.
The treasurer or another officer must manually create each one to group the objects for that year.

Dues package
------------

.. vale Google.Passive = NO
.. vale write-good.E-Prime = NO
.. vale write-good.Passive = NO

A **dues package** represents an option for paying dues.
In the member-facing interface, packages are labeled "Dues Terms."
Dues packages have a few key attributes:

.. vale Google.Ordinal = NO
.. vale Google.Units = NO
.. vale Google.Will = NO

- The **cost** of the package
- The **membership start date**, when someone is considered an active member if they purchase this package.
  Generally, this is August 1st for fall and full year dues, and January 1st for spring dues.
- The **membership end date**, when someone is no longer considered an active member unless/until they purchase another package.
  Generally, this is August 1st for spring or full year dues, and January 1st for fall dues.
- The **access start date**, when someone will gain access to RoboJackets systems if they purchase this package.
  Generally, this matches the membership start date.
- The **access end date**, when someone will lose access to RoboJackets systems unless/until they purchase another package.
  Generally, this is the dues deadline for the following semester.

Other attributes are used to determine whether a package may be offered to a specific person, based on their affiliation with Georgia Tech and prior purchase history.

A dues package may also have links to one or more :ref:`merchandise <Merchandise>` objects, which may be selected by members when choosing that package.

.. attention::
   Officers generally shouldn't create packages manually -- there is an :ref:`action on Fiscal Years<Create Dues Packages>` that sets up default packages.

Merchandise
-----------

A **merchandise** object represents a branded item that's included with a :ref:`dues package <Dues package>`.
This typically includes t-shirts and polos, but other options may be available at the discretion of the officer team.

.. important::
   Officers should fully configure merchandise for a dues package **before** making it available for purchase.
   If new options are added later, prior transactions can't be updated by members directly, and must be updated by an administrator.

Dues transaction
----------------

A **dues transaction** represents a request from a member to purchase a specific :ref:`dues package <Dues package>`.
It may include :ref:`merchandise <Merchandise>` selections, if the package has associated options.

A transaction may have zero or more :doc:`payments </officers/payments/index>` associated.

.. attention::
   Officers **shouldn't** create transactions manually -- they're created automatically through the member-facing interface when a member begins the dues workflow.
