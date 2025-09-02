<?php

return [
    // ...
    'wa_bot' => [
        'url' => env('WA_BOT_URL', 'http://127.0.0.1:3000'),
        'key' => env('WA_BOT_KEY', null), // optional shared secret header
    ],
];
