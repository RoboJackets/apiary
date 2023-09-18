:og:description: Apiary requires prospective members to review and sign a membership agreement before paying dues.

Membership agreements
=====================

.. vale write-good.E-Prime = NO
.. vale write-good.Weasel = NO

Apiary requires prospective members to review and sign a membership agreement before paying dues. This feature is currently implemented using `DocuSign Embedded Signing <https://developers.docusign.com/docs/esign-rest-api/esign101/concepts/embedding/>`_.

Parent or legal guardian signatures
-----------------------------

By default, the agreement only collects a signature from the member themselves.
For members considered a minor by the State of Georgia, a parent or legal guardian must co-sign the agreement.

This feature requires an administrator to enter the parent or legal guardian's name and email address within the administrative interface.

.. vale Google.Parens = NO
.. vale Google.WordList = NO

#. From the Apiary homepage, click the :guilabel:`Admin` link in the top navigation bar.
#. Under the :guilabel:`Other` header in the left sidebar, click :guilabel:`Users`.
#. Search for the user, then click the edit button (pencil icon |editicon|) on their row.
#. Scroll to the :guilabel:`Parent or Guardian Signature` section, then enter **both** the :guilabel:`Parent or Guardian Name` **and** :guilabel:`Parent or Guardian Email`.
#. Scroll to the bottom of the page, then click :guilabel:`Update User`.

.. vale Google.Passive = NO
.. vale Google.Will = NO
.. vale write-good.E-Prime = NO
.. vale write-good.Passive = NO

.. important::

   This change will only apply to **future** envelopes, not envelopes that are already created or completed.

   * If the member has an existing **incomplete** DocuSign envelope, the existing envelope must be **voided**. The member may use the :guilabel:`Decline to Sign` option within DocuSign, or an administrator may use the :guilabel:`Void Envelope` action within Apiary.

   * If the member has already **completed** a DocuSign envelope, an administrator must **delete** the existing envelope within Apiary.

   Any of those methods will reset the membership agreement workflow, and the member must sign again to create a new envelope and send the envelope to their configured parent or guardian.
