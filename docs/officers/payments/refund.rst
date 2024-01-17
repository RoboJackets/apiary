:og:description: Officers can record refunds for cash, check, and Square payments, at the discretion of the treasurer.

Refunding payments
==================

.. vale write-good.E-Prime = NO

Officers can record refunds for cash, check, and Square payments, at the discretion of the treasurer.
Partial refunds aren't supported.

.. vale write-good.Weasel = NO

.. hint::
   To refund payments, you must have the :ref:`refund-payments` permission, which isn't included with any roles.
   If you need access, ask in :slack:`it-helpdesk`.

Refunding :ref:`online payments <Online payment methods>` within Apiary automatically triggers a refund to the original payment method.

Refunding a cash or check payment within Apiary is only a record-keeping operation and doesn't move funds.
The treasurer must send a check or other payment to refund the member.

In either case, the steps within Apiary are the same:

#. From the Apiary homepage, click the :guilabel:`Admin` link in the top navigation bar.
#. Under the :guilabel:`Other` header in the left sidebar, click :guilabel:`Users`.
#. Search for the member using the search box under the :guilabel:`Users` header, then select the user from the results.
#. To refund a payment for dues, scroll to the :guilabel:`Dues Transactions` heading.
   To refund a payment for travel, scroll to the :guilabel:`Travel Assignments` heading.
#. Select the dues transaction or travel assignment from the list.
#. Scroll to the :guilabel:`Payments` heading.
#. Select the payment from the list.
#. Click the Actions menu (three dots |actionsmenu|) to the right of the :guilabel:`Payment Details` header.
#. Select :guilabel:`Refund Payment`.
#. You must provide a reason for the refund.
   If refunding an online payment, this reason is visible to the member on the updated Square receipt.
#. When you're done, click the red :guilabel:`Refund Payment` button.
