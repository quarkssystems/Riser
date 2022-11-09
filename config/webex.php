<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base url
    |--------------------------------------------------------------------------
    |
    | Base url for any api request.
    |
    */
    'base_url' => 'https://webexapis.com/v1/',

    /*
    |--------------------------------------------------------------------------
    | Bearer
    |--------------------------------------------------------------------------
    |
    | Bearer token to authenticate requests.
    |
    */
    'bearer' => env('WEBEX_BEARER', ''),

    /*
    |--------------------------------------------------------------------------
    | Access Token Request
    |--------------------------------------------------------------------------
    |
    | Access token suffix to make request.
    | Gran type for request body
    |
    */
    'access_token' => [
        'url' => 'access_token',
        'grant_type' => 'authorization_code'
    ],

    /*
    |--------------------------------------------------------------------------
    | Access Token Request
    |--------------------------------------------------------------------------
    |
    | Refresh token suffix to make request.
    | Gran type for request body
    |
    */
    'refresh_token' => [
        'url' => 'refresh_token',
        'grant_type' => 'refresh_token'
    ],

    /*
    |--------------------------------------------------------------------------
    | Client information
    |--------------------------------------------------------------------------
    |
    | Information about web integration client
    |
    */
    'client' => [
        'id' => 'Cfe532ac29a4aaaa90f20c2c3b9998d58c8a13831dac25556e0dda62ef366a1d9',
        'secret' => '0572e98d87951ef3e1686d2771a157be40fee89d5c55bc1f47adb45f8b5643de',
        'code' => ''
    ],

    /*
    |--------------------------------------------------------------------------
    | Redirect uri
    |--------------------------------------------------------------------------
    |
    | Redirect url
    |
    */
    'redirect_uri' => 'https://www.riserapp.in/'
];
