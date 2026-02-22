<?php

namespace RichardStyles\EloquentEncryption\Handlers\Traits;

trait HasKeyRotationFallback
{
    /**
     * Decrypt a payload with automatic fallback to previous keys
     *
     * Tries the current key first, then iterates through previous keys
     * if decryption fails. This enables transparent key rotation without
     * requiring re-encryption of existing data.
     *
     * @param  string  $payload  The encrypted payload
     * @return string The decrypted plaintext
     *
     * @throws \Exception If decryption fails with all available keys
     */
    public function decrypt(string $payload): string
    {
        // Try current key first
        try {
            return $this->decryptWithPrivateKey($payload, $this->storage->getPrivateKey());
        } catch (\Exception $e) {
            $originalException = $e;

            // Fall back to previous keys (using structured key pairs)
            foreach ($this->storage->getPreviousKeys() as $keyPair) {
                try {
                    return $this->decryptWithPrivateKey($payload, $keyPair['privatekey']);
                } catch (\Exception $e) {
                    continue;
                }
            }

            throw $originalException;
        }
    }

    /**
     * Decrypt a payload using a specific private key
     *
     * This method must be implemented by the handler to perform
     * algorithm-specific decryption.
     *
     * @param  string  $payload  The encrypted payload
     * @param  string  $privateKey  The private key to use for decryption
     * @return string The decrypted plaintext
     *
     * @throws \Exception If decryption fails
     */
    abstract protected function decryptWithPrivateKey(string $payload, string $privateKey): string;
}
