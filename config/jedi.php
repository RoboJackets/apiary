<?php

declare(strict_types=1);

return [
    /*
     * The endpoint to hit when access needs to be updated
     */
    'host' => env('JEDI_HOST', null),

    /*
     * The token to send with the request
     */
    'token' => env('JEDI_TOKEN', null),
];
