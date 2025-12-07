<?php

return [
    'enabled' => env('WSO2_ENABLED', false),
    'gateway_url' => env('WSO2_GATEWAY_URL'),
    'guest_url' => env('WSO2_GUEST_URL', 'http://localhost:8002/api/v1'),
    'category_url' => env('WSO2_CATEGORY_URL', 'http://localhost:8001/api/v1'),
    'user_url' => env('WSO2_USER_URL', 'http://localhost:8003/api/v1'),
];

