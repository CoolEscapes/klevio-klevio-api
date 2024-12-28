<?php

/*
 * You can place your custom package configuration in here.
 */

/*
|--------------------------------------------------------------------------
| Klevio API Configuration
|--------------------------------------------------------------------------
|
| Configuration for the Klevio API integration including authentication
| and endpoint settings.
|
*/

return [
    // API Authentication
    'client_id' => env('KLEVIO_CLIENT_ID'),
    'api_key' => env('KLEVIO_API_KEY'),
    'private_key' => env('KLEVIO_PRIVATE_KEY'),
    'public_key' => env('KLEVIO_PUBLIC_KEY'),

    // API Settings
    'base_url' => env('KLEVIO_API_URL', 'https://api.klevio.com/v2'),
    'timeout' => env('KLEVIO_API_TIMEOUT', 30),

    // JWT Settings
    'jwt_audience' => 'klevio-api/v2',
    'jwt_lifetime' => 30, // seconds
];