<?php

declare(strict_types=1);

namespace RichardStyles\EloquentEncryption\Handlers;

use Illuminate\Support\Facades\Config;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;
use RichardStyles\EloquentEncryption\Contracts\EncryptionHandler;
use RichardStyles\EloquentEncryption\Contracts\RsaKeyHandler;
use RichardStyles\EloquentEncryption\Handlers\Traits\HasKeyRotationFallback;

class RsaHandler implements EncryptionHandler
{
    use HasKeyRotationFallback;

    public function __construct(
        private RsaKeyHandler $storage,
    ) {}

    /**
     * Generate a new RSA key pair
     *
     * @return array{publickey: string, privatekey: string}
     */
    public function createKeys(string $email = ''): array
    {
        $keyLength = Config::get('eloquent_encryption.key.length', 4096);
        $privateKey = RSA::createKey($keyLength);

        $publicKey = $privateKey->getPublicKey();

        return [
            'privatekey' => (string) $privateKey,
            'publickey' => (string) $publicKey->toString('OpenSSH'),
        ];
    }

    /**
     * Encrypt a plaintext string using RSA with OAEP padding
     */
    public function encrypt(string $plaintext): string
    {
        /** @var \phpseclib3\Crypt\RSA\PublicKey $key */
        $key = $this->loadKey($this->storage->getPublicKey());

        return $key->encrypt($plaintext);
    }

    /**
     * Decrypt a payload using a specific RSA private key
     *
     * @param  string  $payload  The encrypted payload
     * @param  string  $privateKey  The RSA private key
     * @return string The decrypted plaintext
     *
     * @throws \Exception If decryption fails
     */
    protected function decryptWithPrivateKey(string $payload, string $privateKey): string
    {
        /** @var \phpseclib3\Crypt\RSA\PrivateKey $key */
        $key = $this->loadKey($privateKey);

        return $key->decrypt($payload);
    }

    /**
     * Load an RSA key with OAEP padding
     *
     * @return \phpseclib3\Crypt\RSA\PrivateKey|\phpseclib3\Crypt\RSA\PublicKey
     */
    private function loadKey(string $key)
    {
        $loaded = PublicKeyLoader::load($key);

        /** @var \phpseclib3\Crypt\RSA\PrivateKey|\phpseclib3\Crypt\RSA\PublicKey $loaded */
        /** @phpstan-ignore-next-line method exists in phpseclib3 but not detected by static analysis */
        $loaded = $loaded->withPadding(RSA::ENCRYPTION_OAEP);

        return $loaded;
    }
}
