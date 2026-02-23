<?php

declare(strict_types=1);

namespace RichardStyles\EloquentEncryption\Contracts;

use RichardStyles\EloquentEncryption\Exceptions\RSAKeyFileMissing;

interface RsaKeyHandler
{
    /**
     * Have any keys been generated
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
     * Save the generated key pair to the storage location
     */
    public function saveKey($public, $private);

    /**
     * Get the contents of the public key file
     *
     * @return string
     *
     * @throws RSAKeyFileMissing
     */
    public function getPublicKey();

    /**
     * Get the contents of the private key file
     *
     * @return string
     *
     * @throws RSAKeyFileMissing
     */
    public function getPrivateKey();

    /**
     * Get all previous public keys
     *
     * @deprecated Use getPreviousKeys() instead for structured key pairs
     */
    public function getPreviousPublicKeys(): array;

    /**
     * Get all previous private keys
     *
     * @deprecated Use getPreviousKeys() instead for structured key pairs
     */
    public function getPreviousPrivateKeys(): array;

    /**
     * Get all previous key pairs with rotation metadata
     *
     * Returns an array of key pairs, each containing:
     * - 'publickey': The public key content
     * - 'privatekey': The private key content
     * - 'rotated_at': ISO 8601 timestamp of when the key was rotated
     *
     * This keeps key pairs together for auditing purposes. Note that previous
     * public keys are not used for cryptographic operations (only decryption
     * uses previous private keys), but are maintained for audit trails.
     *
     * @return array<int, array{publickey: string, privatekey: string, rotated_at: string}>
     */
    public function getPreviousKeys(): array;

    /**
     * Rotate keys: move current to previous, save new as current
     */
    public function rotateKeys(string $newPublic, string $newPrivate): void;
}
