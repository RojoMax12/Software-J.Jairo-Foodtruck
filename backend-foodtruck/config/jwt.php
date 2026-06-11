<?php

return [
    'secret' => env('JWT_SECRET'),
    'algo' => env('JWT_ALGO', 'HS256'),
    'ttl' => (int) env('JWT_TTL', 60),
    'issuer' => env('JWT_ISSUER', env('APP_URL')),
    'blacklist_enabled' => (bool) env('JWT_BLACKLIST_ENABLED', true),
];