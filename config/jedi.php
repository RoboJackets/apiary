<?php

return [
    /**
     * The endpoint to hit when access needs to be updated
     */
    'endpoint' => env('JEDI_ENDPOINT', null),

    /**
     * The token to send with the request
     */
    'token' => env('JEDI_TOKEN', null),

    /**
     * The team IDs that override access state
     */
    'access_teams' => array_map('intval', explode(',', env('JEDI_ACCESS_TEAMS', ''))),
];
