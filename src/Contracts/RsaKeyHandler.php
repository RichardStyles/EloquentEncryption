<?php


namespace RichardStyles\EloquentEncryption\Contracts;


use RichardStyles\EloquentEncryption\Exceptions\RSAKeyFileMissing;

interface RsaKeyHandler
{
    /**
     * Have any RSA keys been generated
     *
     * @return bool
     */
    public function exists();

    /**
     * A Private key file exists
     *
     * @return bool
     */
    public function hasPrivateKey();

    /**
     * A Public key file exists
     *
     * @return bool
     */
    public function hasPublicKey();

    /**
     * Save the generated RSA key to the storage location
     *
     * @param $public
     * @param $private
     */
    public function saveKey($public, $private);

    /**
     * Get the contents of the public key file
     *
     * @return string
     * @throws RSAKeyFileMissing
     */
    public function getPublicKey();

    /**
     * Get the contents of the private key file
     *
     * @return string
     * @throws RSAKeyFileMissing
     */
    public function getPrivateKey();
}
