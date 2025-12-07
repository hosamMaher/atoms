<?php

return [
    'secret' => env('JWT_SECRET', 'secret123'),
    'expires' => env('JWT_EXPIRE_MINUTES', 1440),
];

