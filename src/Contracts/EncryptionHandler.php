<?php

declare(strict_types=1);

namespace RichardStyles\EloquentEncryption\Contracts;

interface EncryptionHandler
{
    /**
     * Generate a new key pair for this encryption scheme
     *
     * @return array{publickey: string, privatekey: string}
     */
    public function createKeys(string $email = ''): array;

    /**
     * Encrypt a plaintext string
     */
    public function encrypt(string $plaintext): string;

    /**
     * Decrypt a payload back to plaintext
     */
    public function decrypt(string $payload): string;
}
