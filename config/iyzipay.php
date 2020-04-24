<?php

return [
    'baseUrl' => env('IYZIPAY_BASE_URL', 'https://sandbox-api.iyzipay.com'),
    'apiKey' => env('IYZIPAY_API_KEY', ''),
    'secretKey' => env('IYZIPAY_SECRET_KEY', ''),
    'billableModel' => 'App\User'
];
