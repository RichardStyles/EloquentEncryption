<?php

namespace RichardStyles\EloquentEncryption;

use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Support\Facades\Config;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\PublicKeyLoader;
use RichardStyles\EloquentEncryption\Contracts\RsaKeyHandler;
use RichardStyles\EloquentEncryption\Exceptions\InvalidRsaKeyHandler;
use RichardStyles\EloquentEncryption\Exceptions\RSAKeyFileMissing;
use RichardStyles\EloquentEncryption\FileSystem\RsaKeyStorageHandler;

class EloquentEncryption implements Encrypter
{

    /**
     * @var RsaKeyHandler
     */
    private $handler;

    /**
     * ApplicationKey constructor.
     */
    public function __construct()
    {
        $this->handler = app()->make(
            Config::get('eloquent_encryption.handler', RsaKeyStorageHandler::class)
        );

        if (!$this->handler instanceof RsaKeyHandler) {
            throw new InvalidRsaKeyHandler;
        }
    }

    /**
     * Have any RSA keys been generated
     *
     * @return bool
     */
    public function exists()
    {
        return $this->handler->exists();
    }

    /**
     * Generate a set of RSA Keys which will be used to encrypt the database fields
     */
    public function makeEncryptionKeys()
    {
        $key = $this->createKey(Config::get('eloquent_encryption.key.email'));
        $this->handler->saveKey($key['publickey'], $key['privatekey']);
    }

    /**
     * Create a digital set of RSA keys, defaulting to 4096-bit
     *
     * @param string $email
     * @return array
     */
    public function createKey($email = '')
    {
        $keyLength = Config::get('eloquent_encryption.key.length', 4096);
        $privateKey = RSA::createKey($keyLength);

        // Set comment for SSH format if email is provided
        if (!empty($email)) {
            $privateKey = $privateKey->withComment($email);
        }

        $publicKey = $privateKey->getPublicKey();

        return [
            'privatekey' => (string) $privateKey,
            'publickey' => (string) $publicKey->toString('OpenSSH'),
        ];
    }

    /**
     * Helper function to ensure RSA options match for encrypting/decrypting
     *
     * @param $key
     * @return RSA
     */
    private function getRsa($key)
    {
        $rsa = PublicKeyLoader::load($key);
        return $rsa->withPadding(RSA::ENCRYPTION_OAEP);
    }

    /**
     * Encrypt a value using the RSA key
     *
     * @param $value
     * @param bool $serialize
     * @return false|string
     * @throws RSAKeyFileMissing
     */
    public function encrypt($value, $serialize = true)
    {
        return $this->getRsa($this->handler->getPublicKey())
            ->encrypt($serialize ? serialize($value) : $value);
    }

    /**
     * Encrypt a string without serialization.
     *
     * @param string $value
     * @return string
     *
     * @throws RSAKeyFileMissing
     */
    public function encryptString($value)
    {
        return $this->encrypt($value, false);
    }

    /**
     * Decrypt a value using the RSA key
     *
     * @param $value
     * @param bool $unserialize
     * @return false|string|null
     * @throws RSAKeyFileMissing
     */
    public function decrypt($value, $unserialize = true)
    {
        if (empty($value)) {
            return null;
        }

        $decrypted = $this->getRsa($this->handler->getPrivateKey())
            ->decrypt($value);

        return $unserialize ? unserialize($decrypted) : $decrypted;
    }

    /**
     * Decrypt the given string without unserialization.
     *
     * @param string $payload
     * @return string
     *
     * @throws RSAKeyFileMissing
     */
    public function decryptString($payload)
    {
        return $this->decrypt($payload, false);
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->handler, $name)) {
            return $this->handler->{$name}($arguments);
        }
    }

    /**
     * Get the encryption key that the encrypter is currently using.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->handler->getPrivateKey();
    }

    /**
     * Get all encryption keys including the current and previous keys.
     *
     * @return array
     */
    public function getAllKeys()
    {
        return [$this->getKey()];
    }

    /**
     * Get the previous / old encryption keys.
     *
     * @return array
     */
    public function getPreviousKeys()
    {
        return [];
    }
}
