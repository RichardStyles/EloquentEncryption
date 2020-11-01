<?php

namespace RichardStyles\EloquentEncryption;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed decrypt(string $payload, bool $unserialize = true)
 * @method static string decryptString(string $payload)
 * @method static string encrypt(mixed $value, bool $serialize = true)
 * @method static string encryptString(string $value)
 *
 * @see \RichardStyles\EloquentEncryption\EloquentEncryption
 */
class EloquentEncryptionFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'eloquentencryption';
    }
}
