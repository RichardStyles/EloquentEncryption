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
         * The email address used for RSA key generation
         */
        'email' => env('KEY_EMAIL', ''),

        /**
         * The filename for the RSA public key
         */
        'public' => env('KEY_PUBLIC', 'eloquent_encryption.pub'),

        /**
         * The filename for the RSA private key
         */
        'private' => env('KEY_PRIVATE', 'eloquent_encryption'),

        /**
         * Previous key pairs (for rotation support)
         * Each entry should be ['public' => 'filename.pub', 'private' => 'filename']
         * The default handler will manage this via a metadata file,
         * but users can manually configure if using custom handlers
         */
        'previous' => [],

        /**
         * Maximum number of previous keys to maintain
         */
        'max_previous_keys' => env('MAX_PREVIOUS_KEYS', 5),
    ],

    /**
     * This class can be overridden to define how the encryption keys are stored,
     * checked for existence and returned for Encryption and Decryption. This allows
     * for keys to be held in secure Vaults or through another provider.
     *
     * Must implement RichardStyles\EloquentEncryption\Contracts\RsaKeyHandler
     */
    'handler' => \RichardStyles\EloquentEncryption\FileSystem\RsaKeyStorageHandler::class,

    /**
     * The encryption handler that performs the actual cryptographic operations.
     *
     * Supported: RsaHandler (RSA-4096 OAEP), X25519Handler (ECDH + AES-256-GCM)
     *
     * Must implement RichardStyles\EloquentEncryption\Contracts\EncryptionHandler
     */
    'encryptor' => \RichardStyles\EloquentEncryption\Handlers\RsaHandler::class,

];
