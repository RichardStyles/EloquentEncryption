<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'key' => [

        /**
         * The RSA Key Length
         */
        'length' => env('KEY_LENGTH', 4096),

        /**
         * The path in Storage where the keys should be saved.
         * DO NOT SET THIS UNDER app/public or anywhere publicly accessible.
         */
        'store' => env('KEY_STORE', ''),

        /**
         * The filename for the RSA public key
         */
        'public' => env('KEY_PUBLIC', 'eloquent_encryption.pub'),

        /**
         * The filename for the RSA private key
         */
        'private' => env('KEY_PRIVATE', 'eloquent_encryption'),
    ],

    /**
     * This class can be overridden to define how the RSA keys are stored, checked for
     * existence and returned for Encryption and Decryption. This allows for keys to
     * be held in secure Vaults or through another provider.
     */
    'handler' => \RichardStyles\EloquentEncryption\FileSystem\RsaKeyStorageHandler::class,

];
