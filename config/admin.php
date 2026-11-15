<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Seeded Admin Account
    |--------------------------------------------------------------------------
    |
    | Credentials used by AdminDataSeeder to create the portfolio owner's
    | account. They are required in production. Locally, a random password
    | is generated and printed once when ADMIN_PASSWORD is not set.
    |
    */

    'name' => env('ADMIN_NAME', 'Portfolio Admin'),

    'username' => env('ADMIN_USERNAME'),

    'email' => env('ADMIN_EMAIL'),

    'password' => env('ADMIN_PASSWORD'),

];
