<?php

namespace RichardStyles\EloquentEncryption;

use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Support\Facades\Config;
use phpseclib\Crypt\RSA;
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

        if(!$this->handler instanceof RsaKeyHandler){
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
        $rsa = new RSA();
        $rsa->setPublicKeyFormat(RSA::PUBLIC_FORMAT_OPENSSH);
        $rsa->setComment($email);

        return $rsa->createKey(Config::get('eloquent_encryption.key.length', 4096));
    }

    /**
     * Helper function to ensure RSA options match for encrypting/decrypting
     *
     * @param $key
     * @return RSA
     */
    private function getRsa($key)
    {
        $rsa = new RSA();
        $rsa->loadKey($key);
        $rsa->setEncryptionMode(RSA::ENCRYPTION_OAEP);

        return $rsa;
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
     * @param  string  $value
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
     * @param  bool  $unserialize
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
     * @param  string  $payload
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
        if(method_exists($this->handler, $name)){
            return $this->handler->{$name}($arguments);
        }
    }
}
