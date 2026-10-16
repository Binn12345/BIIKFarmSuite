<?php

return [
    'contact' => [
        'email' => env('FARMSUITE_CONTACT_EMAIL'),
        'phone' => env('FARMSUITE_CONTACT_PHONE'),
        'address' => env('FARMSUITE_CONTACT_ADDRESS'),
    ],
    'allow_demo_seeding' => (bool) env('FARMSUITE_ALLOW_DEMO_SEEDING', false),
];
