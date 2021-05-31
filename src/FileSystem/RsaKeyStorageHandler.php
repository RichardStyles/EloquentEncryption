<?php

namespace RichardStyles\EloquentEncryption\FileSystem;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use RichardStyles\EloquentEncryption\Contracts\RsaKeyHandler;
use RichardStyles\EloquentEncryption\Exceptions\RSAKeyFileMissing;

class RsaKeyStorageHandler implements RsaKeyHandler
{
    /**
     * Application Storage instance
     *
     * @var Storage $storage
     */
    private $storage;

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
        $config = config('eloquent_encryption.key');

        $this->storage = Storage::disk($config['store_disk']);

        $this->public_key_path = trim($config['store_path'], '/').'/'.$config['public'];

        $this->private_key_path = trim($config['store_path'], '/').'/'.$config['private'];
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
    public function hasPrivateKey()
    {
        return $this->storage->exists($this->private_key_path);
    }

    /**
     * A Public key file exists
     *
     * @return bool
     */
    public function hasPublicKey()
    {
        return $this->storage->exists($this->public_key_path);
    }

    /**
     * Save the generated RSA key to the storage location
     *
     * @param $public
     * @param $private
     */
    public function saveKey($public, $private)
    {
        $this->storage->put($this->public_key_path, $public);
        $this->storage->put($this->private_key_path, $private);
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

        return $this->storage->get($this->public_key_path);
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

        return $this->storage->get($this->private_key_path);
    }
}
