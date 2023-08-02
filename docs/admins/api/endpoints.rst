:og:description: Apiary provides a REST API that can be used to retrieve and update data within the database.

Endpoints
=========

This list includes the endpoints that are most heavily used and maintained, but is not comprehensive.
If you are looking for an endpoint to perform a specific operation not listed here, ask in :slack:`apiary`.

All endpoints require an ``Authorization`` header with an OAuth access token, and an ``Accept`` header set to ``application/json``.

.. http:get:: /api/v1/user
   :synopsis: Returns information about the authenticated user

   :query string include: additional relationships to include for this user (optional)

   :requestheader Authorization: an OAuth access token (see :ref:`Authentication`)
   :requestheader Accept: ``application/json``

   :status 200: information about the authenticated user
   :status 401: token was either not provided or invalid

.. http:get:: /api/v1/users/(str:identifier)
   :synopsis: Returns information about a specific user

   :parameter string identifier: either a GTID, GT username, or Apiary ID

   :query string include: additional relationships to include for this user (optional)

   :requestheader Authorization: an OAuth access token (see :ref:`Authentication`)
   :requestheader Accept: ``application/json``

   :status 200: the user was located in the database and their information was returned
   :status 404: no user was found with the provided identifier
   :status 401: token was either not provided or invalid

.. http:put:: /api/v1/users/(str:identifier)
   :synopsis: Updates a specific user

   :requestheader Authorization: an OAuth access token (see :ref:`Authentication`)
   :requestheader Accept: ``application/json``

   :status 200: the update was successful and the new information is returned
   :status 401: the token was either not provided or invalid
   :status 403: the authenticated user does not have permission for this operation (either ``update-users`` or ``update-users-own``)

.. http:get:: /api/v1/users/search
   :synopsis: Search for a user

   :query string keyword: either a GT username, first name or GitHub username
   :query string include: additional relationships to include for the users in search results

   :requestheader Authorization: an OAuth access token (see :ref:`Authentication`)
   :requestheader Accept: ``application/json``

   :status 200: results from the search
   :status 401: the token was either not provided or invalid
   :status 403: the authenticated user does not have permission for this operation (``read-users``)

.. http:get:: /api/v1/users/managers
   :synopsis: Return a list of project managers, officers, and other leaders

   :requestheader Authorization: an OAuth access token (see :ref:`Authentication`)
   :requestheader Accept: ``application/json``

   :status 200: the update was successful and the new information is returned
   :status 401: the token was either not provided or invalid
   :status 403: the authenticated user does not have permission for this operation (``read-users``)

.. http:get:: /api/v1/teams
   :synopsis: Return information about all teams

   :requestheader Authorization: an OAuth access token (see :ref:`Authentication`)
   :requestheader Accept: ``application/json``

   :status 200: an array of team information is returned
   :status 401: the token was either not provided or invalid
   :status 403: the authenticated user does not have permission for this operation (``read-teams``)
