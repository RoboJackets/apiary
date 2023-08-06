:og:description: To authenticate to the REST API, an OAuth access token must be provided in the Authorization header.

Authentication
==============

.. vale Google.Passive = NO
.. vale write-good.E-Prime = NO
.. vale write-good.Passive = NO

To authenticate to the REST API, clients must provide an OAuth access token in the ``Authorization`` header, prefixed with ``Bearer``.

Tokens are associated with a specific user, and have the same permissions as the user at the time the request is made.
If creating a token for use by another system, rather than a human user, you should manually create a user within Apiary with the specific permissions required by the system.

To create a new personal access token, navigate to a user's details page within Nova, then use the :guilabel:`Create Personal Access Token` action under the actions menu (three dots |actionsmenu|).

.. vale Google.WordList = NO
.. vale write-good.Weasel = NO

.. note::
   Users with access to Nova can create their own personal access tokens. Users with the :ref:`admin` role can additionally create tokens for **any** user.

.. vale Google.Parens = NO

Access tokens are :abbr:`JWTs (JSON Web Token)` and expire one year after creation.
It's not possible to extend the expiration of a token once created.

To view information about a JWT, you can use a debugger such as https://jwt.io.
If the token is still valid, you can use it against the :http:get:`/api/v1/user` endpoint to return the user information.

.. vale Google.Will = NO

The ``aud`` field is the client ID for the OAuth client that issued this token. For personal access tokens, this will always be a dedicated "Personal Access Client."

The ``sub`` field is the user ID of the user that owns the token.

The ``jti`` field is the token ID for the token within the Apiary database.

