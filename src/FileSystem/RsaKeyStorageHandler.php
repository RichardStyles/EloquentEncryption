<?php


namespace RichardStyles\EloquentEncryption\FileSystem;


use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use RichardStyles\EloquentEncryption\Contracts\RsaKeyHandler;
use RichardStyles\EloquentEncryption\Exceptions\RSAKeyFileMissing;

class RsaKeyStorageHandler implements RsaKeyHandler
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
    public function hasPrivateKey()
    {
        return Storage::exists($this->private_key_path);
    }

    /**
     * A Public key file exists
     *
     * @return bool
     */
    public function hasPublicKey()
    {
        return Storage::exists($this->public_key_path);
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
