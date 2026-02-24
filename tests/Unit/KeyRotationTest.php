<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use RichardStyles\EloquentEncryption\EloquentEncryption;

beforeEach(function () {
    Storage::fake();
    $this->eloquent_encryption = new EloquentEncryption;
});

test('getPreviousKeys returns empty array when no previous keys', function () {
    expect($this->eloquent_encryption->getPreviousKeys())->toBe([]);
});

test('getPreviousKeys returns array of previous keys when they exist', function () {
    // Generate initial keys
    $this->eloquent_encryption->makeEncryptionKeys();

    // Rotate keys to create previous keys
    $this->eloquent_encryption->rotateKeys();

    $previousKeys = $this->eloquent_encryption->getPreviousKeys();

    expect($previousKeys)->toBeArray()
        ->and(count($previousKeys))->toBe(1);
});

test('getAllKeys returns current plus previous keys', function () {
    // Generate initial keys
    $this->eloquent_encryption->makeEncryptionKeys();

    // Should have 1 key (current only)
    expect(count($this->eloquent_encryption->getAllKeys()))->toBe(1);

    // Rotate to create a previous key
    $this->eloquent_encryption->rotateKeys();

    // Should have 2 keys (current + 1 previous)
    expect(count($this->eloquent_encryption->getAllKeys()))->toBe(2);

    // Rotate again
    $this->eloquent_encryption->rotateKeys();

    // Should have 3 keys (current + 2 previous)
    expect(count($this->eloquent_encryption->getAllKeys()))->toBe(3);
});

test('decrypt works with current key', function () {
    $this->eloquent_encryption->makeEncryptionKeys();

    $plaintext = 'Secret data';
    $encrypted = $this->eloquent_encryption->encrypt($plaintext);
    $decrypted = $this->eloquent_encryption->decrypt($encrypted);

    expect($decrypted)->toBe($plaintext);
});

test('decrypt falls back to previous key when current fails', function () {
    // Generate initial keys
    $this->eloquent_encryption->makeEncryptionKeys();

    // Encrypt data with initial key
    $plaintext = 'Secret data encrypted with old key';
    $encrypted = $this->eloquent_encryption->encrypt($plaintext);

    // Rotate keys (old key becomes previous)
    $this->eloquent_encryption->rotateKeys();

    // Should still be able to decrypt data encrypted with old key
    $decrypted = $this->eloquent_encryption->decrypt($encrypted);

    expect($decrypted)->toBe($plaintext);
});

test('decrypt tries all previous keys in order', function () {
    // Generate initial keys
    $this->eloquent_encryption->makeEncryptionKeys();

    // Encrypt with first key
    $plaintext1 = 'Data encrypted with key 1';
    $encrypted1 = $this->eloquent_encryption->encrypt($plaintext1);

    // Rotate to key 2
    $this->eloquent_encryption->rotateKeys();

    // Encrypt with second key
    $plaintext2 = 'Data encrypted with key 2';
    $encrypted2 = $this->eloquent_encryption->encrypt($plaintext2);

    // Rotate to key 3
    $this->eloquent_encryption->rotateKeys();

    // Encrypt with third key (current)
    $plaintext3 = 'Data encrypted with key 3';
    $encrypted3 = $this->eloquent_encryption->encrypt($plaintext3);

    // All should decrypt successfully
    expect($this->eloquent_encryption->decrypt($encrypted1))->toBe($plaintext1)
        ->and($this->eloquent_encryption->decrypt($encrypted2))->toBe($plaintext2)
        ->and($this->eloquent_encryption->decrypt($encrypted3))->toBe($plaintext3);
});

test('decrypt throws exception when all keys fail', function () {
    $this->eloquent_encryption->makeEncryptionKeys();

    // Try to decrypt garbage data
    expect(fn () => $this->eloquent_encryption->decrypt('invalid encrypted data'))
        ->toThrow(Exception::class);
});

test('rotateKeys moves current to previous', function () {
    // Generate initial keys
    $this->eloquent_encryption->makeEncryptionKeys();

    // Get current key before rotation
    $originalKey = $this->eloquent_encryption->getKey();

    // No previous keys yet
    expect(count($this->eloquent_encryption->getPreviousKeys()))->toBe(0);

    // Rotate keys
    $this->eloquent_encryption->rotateKeys();

    // Should have 1 previous key now
    $previousKeys = $this->eloquent_encryption->getPreviousKeys();
    expect(count($previousKeys))->toBe(1)
        ->and($previousKeys[0])->toBe($originalKey);

    // Current key should be different
    expect($this->eloquent_encryption->getKey())->not->toBe($originalKey);
});

test('rotateKeys generates new current key', function () {
    // Generate initial keys
    $this->eloquent_encryption->makeEncryptionKeys();

    $originalKey = $this->eloquent_encryption->getKey();

    // Rotate keys
    $this->eloquent_encryption->rotateKeys();

    $newKey = $this->eloquent_encryption->getKey();

    // Keys should be different
    expect($newKey)->not->toBe($originalKey);

    // New key should work for encryption/decryption
    $plaintext = 'Test data';
    $encrypted = $this->eloquent_encryption->encrypt($plaintext);
    $decrypted = $this->eloquent_encryption->decrypt($encrypted);

    expect($decrypted)->toBe($plaintext);
});

test('rotateKeys respects max_previous_keys limit', function () {
    // Set max to 2 for testing
    Config::set('eloquent_encryption.key.max_previous_keys', 2);

    // Generate initial keys
    $this->eloquent_encryption->makeEncryptionKeys();

    // Encrypt data with first key
    $plaintext1 = 'Data with key 1';
    $encrypted1 = $this->eloquent_encryption->encrypt($plaintext1);

    // Rotate 3 times (will exceed max of 2)
    $this->eloquent_encryption->rotateKeys(); // Previous: [key1], Current: key2
    $this->eloquent_encryption->rotateKeys(); // Previous: [key1, key2], Current: key3
    $this->eloquent_encryption->rotateKeys(); // Previous: [key2, key3], Current: key4 (key1 removed)

    // Should only have 2 previous keys
    expect(count($this->eloquent_encryption->getPreviousKeys()))->toBe(2);

    // Data encrypted with first key should no longer decrypt (key was removed)
    expect(fn () => $this->eloquent_encryption->decrypt($encrypted1))
        ->toThrow(Exception::class);
});

test('metadata file is created on rotation', function () {
    $this->eloquent_encryption->makeEncryptionKeys();
    $this->eloquent_encryption->rotateKeys();

    Storage::assertExists('.eloquent_encryption_metadata.json');

    $metadata = json_decode(Storage::get('.eloquent_encryption_metadata.json'), true);

    expect($metadata)->toHaveKey('current')
        ->and($metadata)->toHaveKey('previous')
        ->and($metadata['previous'])->toBeArray()
        ->and(count($metadata['previous']))->toBe(1);
});

test('previous key files are numbered correctly', function () {
    $this->eloquent_encryption->makeEncryptionKeys();

    // First rotation
    $this->eloquent_encryption->rotateKeys();
    Storage::assertExists('eloquent_encryption.1');
    Storage::assertExists('eloquent_encryption.1.pub');

    // Second rotation
    $this->eloquent_encryption->rotateKeys();
    Storage::assertExists('eloquent_encryption.1');
    Storage::assertExists('eloquent_encryption.1.pub');
    Storage::assertExists('eloquent_encryption.2');
    Storage::assertExists('eloquent_encryption.2.pub');
});

test('storage handler returns structured key pairs with metadata', function () {
    $this->eloquent_encryption->makeEncryptionKeys();

    // Access the storage handler from config
    $storage = app()->make(Config::get('eloquent_encryption.handler'));

    // No previous keys initially
    expect($storage->getPreviousKeys())->toBe([]);

    // Rotate keys
    $this->eloquent_encryption->rotateKeys();

    // Get structured previous keys
    $previousKeys = $storage->getPreviousKeys();

    expect($previousKeys)->toBeArray()
        ->and(count($previousKeys))->toBe(1)
        ->and($previousKeys[0])->toHaveKeys(['publickey', 'privatekey', 'rotated_at'])
        ->and($previousKeys[0]['publickey'])->toBeString()
        ->and($previousKeys[0]['privatekey'])->toBeString()
        ->and($previousKeys[0]['rotated_at'])->toBeString();
});

test('rotated_at timestamp is preserved in metadata', function () {
    $this->eloquent_encryption->makeEncryptionKeys();

    // Access the storage handler from config
    $storage = app()->make(Config::get('eloquent_encryption.handler'));

    // Rotate keys twice
    $this->eloquent_encryption->rotateKeys();
    sleep(1); // Ensure different timestamps
    $this->eloquent_encryption->rotateKeys();

    // Get structured previous keys
    $previousKeys = $storage->getPreviousKeys();

    expect(count($previousKeys))->toBe(2);

    // Both should have rotated_at timestamps
    foreach ($previousKeys as $keyPair) {
        expect($keyPair)->toHaveKey('rotated_at')
            ->and($keyPair['rotated_at'])->not->toBe('unknown');
    }

    // Timestamps should be different (second rotation happened later)
    expect($previousKeys[0]['rotated_at'])->not->toBe($previousKeys[1]['rotated_at']);
});

test('structured key pairs keep public and private keys together', function () {
    $this->eloquent_encryption->makeEncryptionKeys();

    // Get the current keys before rotation
    $storage = app()->make(Config::get('eloquent_encryption.handler'));
    $currentPublic = $storage->getPublicKey();
    $currentPrivate = $storage->getPrivateKey();

    // Rotate keys
    $this->eloquent_encryption->rotateKeys();

    // The previous key pair should contain both the old public and private keys
    $previousKeys = $storage->getPreviousKeys();

    expect(count($previousKeys))->toBe(1)
        ->and($previousKeys[0]['publickey'])->toBe($currentPublic)
        ->and($previousKeys[0]['privatekey'])->toBe($currentPrivate);
});
