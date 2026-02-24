<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;
use RichardStyles\EloquentEncryption\FileSystem\RsaKeyStorageHandler;
use RichardStyles\EloquentEncryption\Handlers\X25519Handler;

beforeEach(function () {
    Storage::fake();
    $this->storage = new RsaKeyStorageHandler;
    $this->handler = new X25519Handler($this->storage);
});

test('can create x25519 key pair', function () {
    $keys = $this->handler->createKeys();

    expect($keys)->toBeArray()
        ->and($keys)->toHaveKeys(['publickey', 'privatekey'])
        ->and($keys['publickey'])->toBeString()
        ->and($keys['privatekey'])->toBeString()
        ->and(base64_decode($keys['publickey'], true))->not->toBeFalse()
        ->and(base64_decode($keys['privatekey'], true))->not->toBeFalse();
});

test('email parameter is ignored for x25519', function () {
    $keysWithoutEmail = $this->handler->createKeys();
    $keysWithEmail = $this->handler->createKeys('test@example.com');

    // Both should work and produce different keys
    expect($keysWithoutEmail)->toBeArray()
        ->and($keysWithEmail)->toBeArray()
        ->and($keysWithoutEmail['publickey'])->not->toBe($keysWithEmail['publickey']);
});

test('can encrypt and decrypt with x25519', function () {
    $keys = $this->handler->createKeys();
    $this->storage->saveKey($keys['publickey'], $keys['privatekey']);

    $plaintext = 'Secret message for X25519';
    $encrypted = $this->handler->encrypt($plaintext);
    $decrypted = $this->handler->decrypt($encrypted);

    expect($decrypted)->toBe($plaintext)
        ->and($encrypted)->not->toBe($plaintext)
        ->and(strlen($encrypted))->toBeGreaterThan(strlen($plaintext));
});

test('encrypted payload contains ephemeral key, nonce, tag and ciphertext', function () {
    $keys = $this->handler->createKeys();
    $this->storage->saveKey($keys['publickey'], $keys['privatekey']);

    $plaintext = 'Test';
    $encrypted = $this->handler->encrypt($plaintext);

    // Payload format: ephemeralPublicKey (32) || nonce (12) || tag (16) || ciphertext
    $minLength = 32 + 12 + 16 + strlen($plaintext);
    expect(strlen($encrypted))->toBeGreaterThanOrEqual($minLength);

    // Verify structure by extracting components
    $ephemeralPublic = substr($encrypted, 0, 32);
    $nonce = substr($encrypted, 32, 12);
    $tag = substr($encrypted, 44, 16);
    $ciphertext = substr($encrypted, 60);

    expect(strlen($ephemeralPublic))->toBe(32)
        ->and(strlen($nonce))->toBe(12)
        ->and(strlen($tag))->toBe(16)
        ->and(strlen($ciphertext))->toBeGreaterThan(0);
});

test('different encryptions of same plaintext produce different ciphertexts', function () {
    $keys = $this->handler->createKeys();
    $this->storage->saveKey($keys['publickey'], $keys['privatekey']);

    $plaintext = 'Same message';
    $encrypted1 = $this->handler->encrypt($plaintext);
    $encrypted2 = $this->handler->encrypt($plaintext);

    // Should be different due to ephemeral keys and random nonces
    expect($encrypted1)->not->toBe($encrypted2);

    // But both should decrypt to the same plaintext
    expect($this->handler->decrypt($encrypted1))->toBe($plaintext)
        ->and($this->handler->decrypt($encrypted2))->toBe($plaintext);
});

test('decrypt falls back to previous keys', function () {
    // Generate initial keys
    $keys1 = $this->handler->createKeys();
    $this->storage->saveKey($keys1['publickey'], $keys1['privatekey']);

    // Encrypt with first key
    $plaintext = 'Data encrypted with old X25519 key';
    $encrypted = $this->handler->encrypt($plaintext);

    // Rotate to new keys
    $keys2 = $this->handler->createKeys();
    $this->storage->rotateKeys($keys2['publickey'], $keys2['privatekey']);

    // Should still decrypt with previous key
    $decrypted = $this->handler->decrypt($encrypted);
    expect($decrypted)->toBe($plaintext);
});

test('decrypt tries multiple previous keys', function () {
    // Generate and encrypt with first key
    $keys1 = $this->handler->createKeys();
    $this->storage->saveKey($keys1['publickey'], $keys1['privatekey']);
    $plaintext1 = 'Data with key 1';
    $encrypted1 = $this->handler->encrypt($plaintext1);

    // Rotate to second key
    $keys2 = $this->handler->createKeys();
    $this->storage->rotateKeys($keys2['publickey'], $keys2['privatekey']);
    $plaintext2 = 'Data with key 2';
    $encrypted2 = $this->handler->encrypt($plaintext2);

    // Rotate to third key
    $keys3 = $this->handler->createKeys();
    $this->storage->rotateKeys($keys3['publickey'], $keys3['privatekey']);

    // All should decrypt
    expect($this->handler->decrypt($encrypted1))->toBe($plaintext1)
        ->and($this->handler->decrypt($encrypted2))->toBe($plaintext2);
});

test('decrypt throws exception for invalid payload', function () {
    $keys = $this->handler->createKeys();
    $this->storage->saveKey($keys['publickey'], $keys['privatekey']);

    $this->handler->decrypt('invalid encrypted data');
})->throws(\Exception::class);

test('decrypt throws exception for wrong key', function () {
    // Encrypt with one key
    $keys1 = $this->handler->createKeys();
    $this->storage->saveKey($keys1['publickey'], $keys1['privatekey']);
    $encrypted = $this->handler->encrypt('Secret');

    // Try to decrypt with different key
    $keys2 = $this->handler->createKeys();
    $this->storage->saveKey($keys2['publickey'], $keys2['privatekey']);

    $this->handler->decrypt($encrypted);
})->throws(\RuntimeException::class);

test('can encrypt and decrypt empty string', function () {
    $keys = $this->handler->createKeys();
    $this->storage->saveKey($keys['publickey'], $keys['privatekey']);

    $plaintext = '';
    $encrypted = $this->handler->encrypt($plaintext);
    $decrypted = $this->handler->decrypt($encrypted);

    expect($decrypted)->toBe($plaintext);
});

test('can encrypt and decrypt unicode characters', function () {
    $keys = $this->handler->createKeys();
    $this->storage->saveKey($keys['publickey'], $keys['privatekey']);

    $plaintext = '🔐 Encrypted émojis and spëcial çharacters 日本語';
    $encrypted = $this->handler->encrypt($plaintext);
    $decrypted = $this->handler->decrypt($encrypted);

    expect($decrypted)->toBe($plaintext);
});

test('can encrypt large data', function () {
    $keys = $this->handler->createKeys();
    $this->storage->saveKey($keys['publickey'], $keys['privatekey']);

    // 10KB of data
    $plaintext = str_repeat('Large data block. ', 500);
    $encrypted = $this->handler->encrypt($plaintext);
    $decrypted = $this->handler->decrypt($encrypted);

    expect($decrypted)->toBe($plaintext);
});

test('decrypt with corrupted payload throws exception', function () {
    $keys = $this->handler->createKeys();
    $this->storage->saveKey($keys['publickey'], $keys['privatekey']);

    $encrypted = $this->handler->encrypt('Original message');

    // Corrupt the payload by modifying the tag
    $corrupted = substr($encrypted, 0, 44).'X'.substr($encrypted, 45);

    $this->handler->decrypt($corrupted);
})->throws(\RuntimeException::class, 'authentication tag mismatch');
