<?php

declare(strict_types=1);

namespace RichardStyles\EloquentEncryption;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool exists()
 * @method static void makeEncryptionKeys()
 * @method static array{publickey: string, privatekey: string} createKey(string $email = '')
 * @method static mixed decrypt(string $payload, bool $unserialize = true)
 * @method static string decryptString(string $payload)
 * @method static string encrypt(mixed $value, bool $serialize = true)
 * @method static string encryptString(string $value)
 * @method static string getKey()
 * @method static array<int, string> getAllKeys()
 * @method static array<int, string> getPreviousKeys()
 * @method static void rotateKeys()
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
