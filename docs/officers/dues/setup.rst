:og:description: While dues collection itself is largely automated, there are a few steps that need to be performed manually by the treasurer or another officer to start the process.

Setting Up Dues
===============

While dues collection itself is largely automated, there are a few steps that need to be performed manually by the treasurer or another officer to start the process.

.. hint::
   These functions are restricted to core officers.
   If you need access, ask in :slack:`it-helpdesk`.

Create a Fiscal Year
--------------------

#. From the Apiary homepage, click the :guilabel:`Admin` link in the top navigation bar.
#. Under the :guilabel:`Dues` header in the left sidebar, click :guilabel:`Fiscal Years`.
#. Click the blue :guilabel:`Create Fiscal Year` button above the top-right of the list.
#. Enter the :guilabel:`Ending Year`. For example, for the fiscal year ending in June 2023, enter 2023.
#. Click the blue :guilabel:`Create Fiscal Year` button below the bottom-right of the form.

Your newly-created fiscal year won't have any packages or merchandise associated with it yet.
Use the :guilabel:`Create Dues Packages` action to create default packages and merchandise, then adjust as needed.

Create Dues Packages
--------------------

#. Click the Actions menu (three dots |actionsmenu|) to the right of the :guilabel:`Fiscal Year Details` header, then choose the :guilabel:`Create Dues Packages` option.
#. A confirmation popup will appear with an option to create a non-student package.
   In general, you should leave this option enabled, but you can disable it if you wish.
#. Click the blue :guilabel:`Create Packages` button.

.. note::
   Only students can view or purchase student packages, and only non-students can view or purchase non-student packages.

Your new fiscal year will now have default dues packages and merchandise set up and linked appropriately.

Set Dues Deadlines
------------------

If you reviewed the :doc:`/officers/dues/data-model`, you may have noticed there is no explicit dues deadline.
In Apiary, dues deadlines are modeled using the :guilabel:`Access End Date` field.
You will need to update *older* dues packages, so that their access end date is set to the dues deadline.

In general, the access end date for **spring** and **full-year** packages should be set to the **fall** dues deadline, and the access end date for the **fall** package should be set to the **spring** dues deadline.
