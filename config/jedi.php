<?php

declare(strict_types=1);

return [
    /*
     * The endpoint to hit when access needs to be updated
     */
    'endpoint' => env('JEDI_ENDPOINT', null),

    /*
     * The token to send with the request
     */
    'token' => env('JEDI_TOKEN', null),
];
