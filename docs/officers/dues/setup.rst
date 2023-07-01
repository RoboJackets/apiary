:og:description: While dues collection itself is largely automated, there are a few steps that need to be performed manually by the treasurer or another officer to start the process.

Setting Up Dues
===============

While dues collection itself is largely automated, there are a few steps that need to be performed manually by the treasurer or another officer to start the process.

.. hint::
   If you're not able to see the options described here, you may need additional access. Ask in :slack:`it-helpdesk`!

Create a Fiscal Year
--------------------

1. From the Apiary homepage, click the **Admin** link in the top navigation bar.
2. Under the **Dues** header in the left sidebar, click **Fiscal Years**.
3. Click the blue **Create Fiscal Year** button above the top-right of the list.
4. Enter the ending year. For example, for the fiscal year ending in June 2023, enter 2023.
5. Click the blue **Create Fiscal Year** button below the bottom-right of the form.

Your newly-created fiscal year won't have any packages or merchandise associated with it yet.
Use the "Create Dues Packages" action to create default packages and merchandise, then adjust as needed.

Create Dues Packages
--------------------

.. raw:: html

    <ol class="arabic simple">
        <li>
            <p>
                Click the Actions menu (three dots <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -5 20 20" fill="currentColor" width="20" height="20" class="inline" role="presentation"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"></path></svg>) to the right of the <strong>Fiscal Year Details</strong> header, then choose the <strong>Create Dues Packages</strong> option.
            </p>
        </li>
        <li>
            <p>
                A confirmation popup will appear with an option to create a non-student package. In general, you should leave this option enabled, but you can disable it if you wish.
            </p>
        </li>
        <li>
            <p>
                Click the blue <strong>Create Packages</strong> button.
            </p>
        </li>
    </ol>

.. note::
   Only students can view or purchase student packages, and only non-students can view or purchase non-student packages.

Your new fiscal year will now have default dues packages and merchandise set up and linked appropriately.

Set Dues Deadlines
------------------

If you reviewed the :doc:`data-model`, you may have noticed there is no explicit dues deadline.
In Apiary, dues deadlines are modeled using the **Access End Date** field.
You will need to update *older* dues packages, so that their access end date is set to the dues deadline.

In general, the access end date for **spring** and **full-year** packages should be set to the **fall** dues deadline, and the access end date for the **fall** package should be set to the **spring** dues deadline.
