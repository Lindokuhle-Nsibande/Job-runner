<?php

return [
    'allowed' => [
        App\Http\Controllers\Example::class => ['handle'],
    ],
    'retry' => [
        'attempts' => 3,
        'delay' => 60,
    ],
];