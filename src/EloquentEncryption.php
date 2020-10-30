<?php

namespace RichardStyles\EloquentEncryption;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use phpseclib\Crypt\RSA;
use RichardStyles\EloquentEncryption\Contracts\RsaKeyHandler;
use RichardStyles\EloquentEncryption\Exceptions\RSAKeyFileMissing;
use RichardStyles\EloquentEncryption\FileSystem\RsaKeyStorageHandler;

class EloquentEncryption
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
     * @return false|string
     * @throws RSAKeyFileMissing
     */
    public function encrypt($value)
    {
        return $this->getRsa($this->handler->getPublicKey())
            ->encrypt($value);
    }

    /**
     * Decrypt a value using the RSA key
     *
     * @param $value
     * @return false|string|null
     * @throws RSAKeyFileMissing
     */
    public function decrypt($value)
    {
        if (empty($value)) {
            return null;
        }

        return $this->getRsa($this->handler->getPrivateKey())
            ->decrypt($value);
    }

    public function __call($name, $arguments)
    {
        if(method_exists($this->handler, $name)){
            return $this->handler->{$name}($arguments);
        }
    }
}
