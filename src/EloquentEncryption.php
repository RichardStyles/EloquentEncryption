<?php

declare(strict_types=1);

namespace RichardStyles\EloquentEncryption;

use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Support\Facades\Config;
use RichardStyles\EloquentEncryption\Contracts\EncryptionHandler;
use RichardStyles\EloquentEncryption\Contracts\RsaKeyHandler;
use RichardStyles\EloquentEncryption\Exceptions\InvalidRsaKeyHandler;
use RichardStyles\EloquentEncryption\FileSystem\RsaKeyStorageHandler;
use RichardStyles\EloquentEncryption\Handlers\RsaHandler;

class EloquentEncryption implements Encrypter
{
    /**
     * The key storage handler (manages key files)
     */
    private RsaKeyHandler $storage;

    /**
     * The encryption handler (performs crypto)
     */
    private EncryptionHandler $encryptor;

    public function __construct()
    {
        $storage = app()->make(
            Config::get('eloquent_encryption.handler', RsaKeyStorageHandler::class)
        );

        if (! $storage instanceof RsaKeyHandler) {
            throw new InvalidRsaKeyHandler;
        }

        $this->storage = $storage;

        $encryptorClass = Config::get('eloquent_encryption.encryptor', RsaHandler::class);
        $this->encryptor = new $encryptorClass($this->storage);
    }

    /**
     * Have any encryption keys been generated
     */
    public function exists(): bool
    {
        return $this->storage->exists();
    }

    /**
     * Generate a set of encryption keys
     */
    public function makeEncryptionKeys(): void
    {
        $key = $this->encryptor->createKeys(Config::get('eloquent_encryption.key.email', ''));
        $this->storage->saveKey($key['publickey'], $key['privatekey']);
    }

    /**
     * Create a new key pair (delegates to encryption handler)
     *
     * @param  string  $email
     * @return array{publickey: string, privatekey: string}
     */
    public function createKey($email = ''): array
    {
        return $this->encryptor->createKeys($email);
    }

    /**
     * Encrypt a value
     *
     * @param  mixed  $value
     * @param  bool  $serialize
     * @return string
     */
    public function encrypt($value, $serialize = true)
    {
        return $this->encryptor->encrypt($serialize ? serialize($value) : $value);
    }

    /**
     * Encrypt a string without serialization.
     *
     * @param  string  $value
     * @return string
     */
    public function encryptString($value)
    {
        return $this->encrypt($value, false);
    }

    /**
     * Decrypt a value
     *
     * @param  mixed  $value
     * @param  bool  $unserialize
     * @return mixed
     */
    public function decrypt($value, $unserialize = true)
    {
        if (empty($value)) {
            return null;
        }

        $decrypted = $this->encryptor->decrypt($value);

        return $unserialize ? unserialize($decrypted) : $decrypted;
    }

    /**
     * Decrypt the given string without unserialization.
     *
     * @param  string  $payload
     * @return string
     */
    public function decryptString($payload)
    {
        return $this->decrypt($payload, false);
    }

    /**
     * @param  array<int, mixed>  $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (method_exists($this->storage, $name)) {
            return $this->storage->{$name}($arguments);
        }

        return null;
    }

    /**
     * Get the encryption key that the encrypter is currently using.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->storage->getPrivateKey();
    }

    /**
     * Get all encryption keys including the current and previous keys.
     *
     * @return array<int, string>
     */
    public function getAllKeys(): array
    {
        return array_merge(
            [$this->getKey()],
            $this->getPreviousKeys()
        );
    }

    /**
     * Get the previous / old encryption keys.
     *
     * @return array<int, string>
     */
    public function getPreviousKeys(): array
    {
        return $this->storage->getPreviousPrivateKeys();
    }

    /**
     * Rotate encryption keys: generate new keys and move current to previous
     */
    public function rotateKeys(): void
    {
        $newKeys = $this->encryptor->createKeys(Config::get('eloquent_encryption.key.email', ''));
        $this->storage->rotateKeys($newKeys['publickey'], $newKeys['privatekey']);
    }
}
