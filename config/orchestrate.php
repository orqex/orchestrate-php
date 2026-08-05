<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Secret API key
    |--------------------------------------------------------------------------
    |
    | Your Orqex secret key (starts with "sk_"). Issue keys from the Orqex
    | dashboard. Keep it out of source control via your .env file.
    |
    */
    'secret_key' => env('ORCHESTRATE_SECRET_KEY', env('ORQEX_SECRET_KEY')),

    /*
    |--------------------------------------------------------------------------
    | API base URI
    |--------------------------------------------------------------------------
    */
    'base_uri' => env('ORCHESTRATE_BASE_URI', 'https://api.orqex.com/v1'),

    /*
    |--------------------------------------------------------------------------
    | HTTP behaviour
    |--------------------------------------------------------------------------
    |
    | Total/connect timeouts in seconds and the number of automatic retries
    | on transient failures (connection errors, 429 and 5xx responses).
    |
    */
    'timeout' => (float) env('ORCHESTRATE_TIMEOUT', 30),

    'connect_timeout' => (float) env('ORCHESTRATE_CONNECT_TIMEOUT', 10),

    'max_retries' => (int) env('ORCHESTRATE_MAX_RETRIES', 2),
];
