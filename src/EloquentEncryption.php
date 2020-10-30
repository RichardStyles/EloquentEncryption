<?php

namespace RichardStyles\EloquentEncryption;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use phpseclib\Crypt\RSA;
use RichardStyles\EloquentEncryption\Exceptions\RSAKeyFileMissing;

class EloquentEncryption
{

    /**
     * Storage path for the Public Key File
     *
     * @var string
     */
    private $public_key_path;

    /**
     * Storage path for the Private Key File
     *
     * @var string
     */
    private $private_key_path;

    /**
     * ApplicationKey constructor.
     */
    public function __construct()
    {
        $this->public_key_path =
            Config::get('eloquent_encryption.key.public', 'eloquent_encryption.pub');
        $this->private_key_path =
            Config::get('eloquent_encryption.key.private', 'eloquent_encryption');
    }

    /**
     * Have any RSA keys been generated
     *
     * @return bool
     */
    public function exists()
    {
        return $this->hasPrivateKey() && $this->hasPublicKey();
    }

    /**
     * A Private key file exists
     *
     * @return bool
     */
    protected function hasPrivateKey()
    {
        return Storage::exists($this->private_key_path);
    }

    /**
     * A Public key file exists
     *
     * @return bool
     */
    protected function hasPublicKey()
    {
        return Storage::exists($this->public_key_path);
    }

    /**
     * Generate a set of RSA Keys which will be used to encrypt the database fields
     */
    public function makeEncryptionKeys()
    {
        $key = $this->createKey(Config::get('eloquent_encryption.key.email'));
        $this->saveKey($key['publickey'], $key['privatekey']);
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
     * Save the generated RSA key to the storage location
     *
     * @param $public
     * @param $private
     */
    public function saveKey($public, $private)
    {
        Storage::put($this->public_key_path, $public);
        Storage::put($this->private_key_path, $private);
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
        return $this->getRsa($this->getPublicKey())
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

        $rsa = new RSA();
        $rsa->loadKey($this->getPrivateKey());
        $rsa->setEncryptionMode(RSA::ENCRYPTION_OAEP);

        return $this->getRsa($this->getPrivateKey())
            ->decrypt($value);
    }

    /**
     * Get the contents of the public key file
     *
     * @return string
     * @throws RSAKeyFileMissing
     */
    public function getPublicKey()
    {
        if (!$this->hasPublicKey()) {
            throw new RSAKeyFileMissing();
        }

        return Storage::get($this->public_key_path);
    }

    /**
     * Get the contents of the private key file
     *
     * @return string
     * @throws RSAKeyFileMissing
     */
    public function getPrivateKey()
    {
        if (!$this->hasPrivateKey()) {
            throw new RSAKeyFileMissing();
        }

        return Storage::get($this->private_key_path);
    }
}
