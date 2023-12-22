:og:description: Apiary integrates with several external services that require configuration outside of the app itself.

External services
=================

Apiary integrates with several external services that require configuration outside of the app itself.

.. vale Google.Headings = NO
.. vale write-good.E-Prime = NO
.. vale write-good.Passive = NO
.. vale Google.Passive = NO

Central Authentication Service
------------------------------

.. vale Google.Acronyms = NO
.. vale Google.Parens = NO
.. vale Google.WordList = NO

Apereo :abbr:`CAS (Central Authentication Service)` is the :abbr:`OIT (Office of Information Technology)`-hosted and managed single sign-on service that allows members to authenticate to Apiary with their usual Georgia Tech username and password.
CAS access may be requested from OIT Identity and Access Management within `ServiceNow <https://gatech.service-now.com/technology?id=sc_cat_item&sys_id=efa8f2601bc2e050a8622f4b234bcb2f&sysparm_category=c34751bd1bb964d0a8622f4b234bcb92>`_.

.. vale Google.Acronyms = YES
.. vale Google.Parens = YES
.. vale Google.WordList = YES

The following attributes must be returned:

- ``gtGTID``
- ``email_primary``
- ``givenName``
- ``sn``

The production CAS service requires the following configuration within Apiary:

- ``CAS_HOSTNAME`` must be set to ``sso.gatech.edu``
- ``CAS_REAL_HOSTS`` must be set to ``sso.gatech.edu``
- ``CAS_PORT`` must be set to ``443``
- ``CAS_URI`` must be set to ``/cas``
- ``CAS_CLIENT_SERVICE`` must be set to the fully qualified URL for the Apiary instance
- ``CAS_VALIDATION`` must be set to ``ca``
- ``CAS_CERT`` must be set to the USERTrust root certificate file location
- ``CAS_VALIDATE_CN`` must be set to ``true``
- ``CAS_LOGOUT_URL`` must be set to ``https://sso.gatech.edu/cas/logout``
- ``CAS_LOGOUT_REDIRECT`` must be ``null``
- ``CAS_ENABLE_SAML`` must be set to ``false``
- ``CAS_VERSION`` must be set to ``3.0``

BuzzAPI
-------

BuzzAPI is an OIT-hosted and managed service that allows Apiary to look up individuals based on their GTID, among other uses.
BuzzAPI access may be requested from OIT Identity and Access Management within `ServiceNow <https://gatech.service-now.com/technology?id=sc_cat_item&sys_id=c981906a1b712014a8622f4b234bcb83&sysparm_category=c34751bd1bb964d0a8622f4b234bcb92>`_.

.. vale Google.Will = NO

The BuzzAPI service account must have access to search ``central.iam.gted.accounts``.
Apiary will use either a GTID, username, or ``gtPersonDirectoryID`` depending on which is available.

The following attributes must be returned:

- ``gtGTID``
- ``mail``
- ``sn``
- ``givenName``
- ``gtPrimaryGTAccountUsername``
- ``uid``

BuzzAPI requires the following configuration within Apiary:

- ``BUZZAPI_HOST`` must be set to ``api.gatech.edu``
- ``BUZZAPI_APP_ID`` must be set to the username used to access BuzzAPI
- ``BUZZAPI_APP_PASSWORD`` must be set to the password used to access BuzzAPI

In some cases, it may not be desirable to use a real BuzzAPI server.
You can enable and use a mock endpoint by setting the following options:

- ``FEATURE_SANDBOX_MODE`` must be set to ``true``
- ``BUZZAPI_HOST`` must be set to the Apiary instance's hostname
- ``BUZZAPI_APP_ID`` must be a randomly generated secret value
- ``BUZZAPI_APP_PASSWORD`` must be a randomly generated secret value

Note that the mock endpoint uses the app's internal database to look up users, and can return real data in some cases.

DocuSign
--------

Apiary uses `DocuSign Embedded Signing <https://developers.docusign.com/docs/esign-rest-api/esign101/concepts/embedding/>`_ for :doc:`membership agreements </admins/membership-agreements>`.

For development and testing, a `DocuSign Developer account <https://developers.docusign.com/>`_ can be used.

To set up an app within Georgia Tech's DocuSign account, contact `OIT Enterprise Apps and Data Management <https://esignature.gatech.edu/esigsupport/devl.cfm>`_.

DocuSign requires the following configuration within Apiary:

.. vale Google.Parens = NO
.. vale write-good.Weasel = NO

- ``DOCUSIGN_CLIENT_ID`` must be set to the client ID, also known as the integration key
- ``DOCUSIGN_CLIENT_SECRET`` must be set to the client secret, also known as the secret key
- ``DOCUSIGN_API_BASE_PATH`` must be set to the base path for the DocuSign API server
    - For the demo environment, this value is always ``https://demo.docusign.net/restapi``
    - For Georgia Tech's production environment, this value is ``https://na3.docusign.net/restapi``
- ``DOCUSIGN_ACCOUNT_ID`` must be set to the account where the app is registered, also known as the API account ID
- ``DOCUSIGN_IMPERSONATE_USER_ID`` must be set to the user ID that will be impersonated for sending membership agreements
- ``DOCUSIGN_PRIVATE_KEY`` must be set to the RSA private key in :abbr:`PEM (Privacy-Enhanced Mail)` format
- ``DOCUSIGN_MEMBERSHIP_AGREEMENT_MEMBER_ONLY_TEMPLATE_ID`` must be set to the template ID for membership agreements where only the member must sign
- ``DOCUSIGN_MEMBERSHIP_AGREEMENT_MEMBER_AND_GUARDIAN_TEMPLATE_ID`` must be set to the template ID for membership agreements where both the member and a parent or guardian must sign

.. vale Google.Parens = YES
.. vale write-good.Weasel = YES

Postmark
--------

Apiary sends transactional emails to remind members about mandatory tasks, as well as receipts and DocuSign acknowledgement emails.
While Laravel supports a wide variety of email service providers, RoboJackets uses `Postmark <https://postmarkapp.com/>`_.

Postmark requires the following configuration within Apiary:

- ``MAIL_MAILER`` must be set to ``postmark``
- ``MAIL_FROM_ADDRESS`` must be set to the ``From`` address used to send emails
    - This address must be either individually verified within Postmark or under a verified domain
- ``MAIL_FROM_NAME`` will be the display name shown to email recipients
- ``POSTMARK_TOKEN`` must be set to the server API token
- ``POSTMARK_MESSAGE_STREAM_ID`` must be set to the stream ID used to send emails
- ``POSTMARK_OUTBOUND_TOKEN`` must be set to a randomly generated secret value and used as the ``X-Postmark-Token`` header for webhooks
    - This enables Postmark to notify Apiary of bounces and subscription changes, which are then persisted on user records to suppress further emails.

Webhooks should be sent to ``/api/v1/postmark/outbound`` with a custom header of ``X-Postmark-Token`` with the value matching ``POSTMARK_OUTBOUND_TOKEN``.

Laravel Nova
------------

Apiary uses `Laravel Nova <https://nova.laravel.com/>`_ to build the administrator-facing web interface.
Nova is commercial software, and requires a license key to be provided in the ``NOVA_LICENSE_KEY`` environment variable to remove the red :guilabel:`UNREGISTERED` text in the navigation bar.

Sentry
------

Apiary uses `Sentry <https://sentry.io/welcome/>`_ for monitoring errors and app performance.
While not strictly required, it's helpful for the development team to receive information about all deployed instances.

Sentry requires the following configuration within Apiary:

.. vale Google.Parens = NO

- ``SENTRY_LARAVEL_DSN`` must be set to the :abbr:`DSN (data source name)` for the Sentry project
- ``CSP_REPORT_URI`` must be set to the Content Security Policy report URI for the Sentry project
- ``DOCKER_IMAGE_DIGEST`` must be set to an identifier for the release version - if running in a Docker container, use the image digest

.. vale Google.Parens = YES

GitHub
------

OAuth credentials must be provided to enable linking a `GitHub <https://github.com>`_ account within Apiary.
See the `GitHub documentation <https://docs.github.com/en/apps/creating-github-apps/registering-a-github-app/registering-a-github-app>`_ for more details on registering a GitHub App.

- ``GITHUB_CLIENT_ID`` must be set to the client ID
- ``GITHUB_CLIENT_SECRET`` must be set to the client secret

Google
------

OAuth credentials must be provided to enable linking a `Google Account <https://www.google.com/account/about/>`_ within Apiary.
See the `Google developer documentation <https://developers.google.com/identity/sign-in/web/sign-in>`_ for more details.

- ``GOOGLE_CLIENT_ID`` must be set to the client ID
- ``GOOGLE_CLIENT_SECRET`` must be set to the client secret

Square
------

Apiary uses `Square <https://squareup.com/us/en>`_ for collecting payments.
See the `Square developer documentation <https://developer.squareup.com/us/en>`_ for more details on registering an app.

Square requires the following configuration within Apiary:

- ``SQUARE_ACCESS_TOKEN`` must be set to the access token
- ``SQUARE_LOCATION_ID`` must be set to the location where payments should be attributed
- ``SQUARE_ENVIRONMENT`` must be set to either ``production`` or ``sandbox``
- ``SQUARE_WEBHOOK_SIGNATURE_KEY`` must be set to the webhook signature key

Webhooks should be sent to ``/api/v1/square`` for ``payment.created`` and ``payment.updated`` events.

Full OAuth authentication with merchant accounts isn't supported.

JEDI
----

Apiary can optionally integrate with `JEDI <https://github.com/RoboJackets/jedi>`_ to support propagating changes within Apiary to a variety of other services.

JEDI requires the following configuration within Apiary:

- ``JEDI_HOST`` must be the base URL for the JEDI server
- ``JEDI_TOKEN`` must be the token to use to authenticate to JEDI
