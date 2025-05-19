<?php

declare(strict_types = 1);

return [
    /*
    |--------------------------------------------------------------------------
    | Enable Bind
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the connections below you wish to use as
    | your default connection for all work. Of course, you may use many
    | connections at once using the manager class.
    |
    */

    'max_days' => env('MAX_DAYS_RULE', 30),
];
