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
    ],

    /**
     * This class can be overridden to define how the RSA keys are stored, checked for existance and returned
     * for Encryption and Decryption. This allows for keys to be held in secure Vaults or through another
     * Service provider.
     */
    'handler' => \RichardStyles\EloquentEncryption\FileSystem\RsaKeyStorageHandler::class,

];
