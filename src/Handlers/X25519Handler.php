<?php

declare(strict_types=1);

namespace RichardStyles\EloquentEncryption\Handlers;

use phpseclib3\Crypt\DH;
use phpseclib3\Crypt\EC;
use RichardStyles\EloquentEncryption\Contracts\EncryptionHandler;
use RichardStyles\EloquentEncryption\Contracts\RsaKeyHandler;
use RichardStyles\EloquentEncryption\Handlers\Traits\HasKeyRotationFallback;

class X25519Handler implements EncryptionHandler
{
    use HasKeyRotationFallback;

    public function __construct(
        private RsaKeyHandler $storage,
    ) {}

    /**
     * Generate a new X25519 key pair
     *
     * @param  string  $email  Not used for X25519, kept for interface compatibility
     * @return array{publickey: string, privatekey: string}
     */
    public function createKeys(string $email = ''): array
    {
        $privateKey = EC::createKey('curve25519');
        $publicKey = $privateKey->getPublicKey();

        return [
            'privatekey' => base64_encode($privateKey->toString('MontgomeryPrivate')),
            'publickey' => base64_encode($publicKey->toString('MontgomeryPublic')),
        ];
    }

    /**
     * Encrypt a plaintext string using X25519 ECDH + AES-256-GCM
     *
     * Generates an ephemeral X25519 keypair, performs ECDH with the stored
     * public key to derive a shared secret, then encrypts with AES-256-GCM.
     *
     * Payload format: ephemeralPublicKey (32) || nonce (12) || tag (16) || ciphertext
     */
    public function encrypt(string $plaintext): string
    {
        // Load the stored static public key
        $storedPublicRaw = base64_decode($this->storage->getPublicKey());
        $storedPublic = EC::loadFormat('MontgomeryPublic', $storedPublicRaw);

        // Generate ephemeral keypair
        $ephemeralPrivate = EC::createKey('curve25519');
        $ephemeralPublicRaw = $ephemeralPrivate->getPublicKey()->toString('MontgomeryPublic');

        // ECDH key agreement
        /** @var string $sharedSecret */
        /** @phpstan-ignore-next-line phpseclib3 DH::computeSecret accepts EC keys for curve25519 */
        $sharedSecret = DH::computeSecret($ephemeralPrivate, $storedPublic);

        // Derive AES-256 key from shared secret
        $aesKey = hash_hkdf('sha256', $sharedSecret, 32, 'eloquent-encryption-x25519');

        // Encrypt with AES-256-GCM
        $nonce = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $aesKey, OPENSSL_RAW_DATA, $nonce, $tag, '', 16);

        // Pack: ephemeralPublicKey (32) || nonce (12) || tag (16) || ciphertext
        return $ephemeralPublicRaw.$nonce.$tag.$ciphertext;
    }

    /**
     * Decrypt a payload using a specific X25519 private key
     *
     * @param  string  $payload  The encrypted payload
     * @param  string  $base64PrivateKey  The base64-encoded X25519 private key
     * @return string The decrypted plaintext
     *
     * @throws \RuntimeException If decryption fails
     */
    protected function decryptWithPrivateKey(string $payload, string $base64PrivateKey): string
    {
        // Unpack: ephemeralPublicKey (32) || nonce (12) || tag (16) || ciphertext
        $ephemeralPublicRaw = substr($payload, 0, 32);
        $nonce = substr($payload, 32, 12);
        $tag = substr($payload, 44, 16);
        $ciphertext = substr($payload, 60);

        // Load our stored private key
        $storedPrivateRaw = base64_decode($base64PrivateKey);
        $storedPrivate = EC::loadFormat('MontgomeryPrivate', $storedPrivateRaw);

        // Load ephemeral public key
        $ephemeralPublic = EC::loadFormat('MontgomeryPublic', $ephemeralPublicRaw);

        // ECDH key agreement (same shared secret as encryption)
        /** @var string $sharedSecret */
        /** @phpstan-ignore-next-line phpseclib3 DH::computeSecret accepts EC keys for curve25519 */
        $sharedSecret = DH::computeSecret($storedPrivate, $ephemeralPublic);

        // Derive same AES key
        $aesKey = hash_hkdf('sha256', $sharedSecret, 32, 'eloquent-encryption-x25519');

        // Decrypt with AES-256-GCM
        $plaintext = openssl_decrypt($ciphertext, 'aes-256-gcm', $aesKey, OPENSSL_RAW_DATA, $nonce, $tag);

        if ($plaintext === false) {
            throw new \RuntimeException('X25519 decryption failed: authentication tag mismatch');
        }

        return $plaintext;
    }
}
