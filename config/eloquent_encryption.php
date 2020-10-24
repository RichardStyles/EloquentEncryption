<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'key' => [

        'length' => env('KEY_LENGTH', 4096),

        'store' => env('KEY_STORE', ''),

        'public' => env('KEY_PUBLIC', 'eloquent_encryption.pub'),

        'private' => env('KEY_PRIVATE', 'eloquent_encryption'),
    ]
];
