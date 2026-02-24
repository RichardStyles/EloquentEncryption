<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use RichardStyles\EloquentEncryption\EloquentEncryption;

beforeEach(function () {
    Storage::fake();
    $this->eloquent_encryption = new EloquentEncryption;
});

test('full key rotation workflow maintains data accessibility', function () {
    // Step 1: Generate initial keys
    $this->eloquent_encryption->makeEncryptionKeys();
    expect($this->eloquent_encryption->exists())->toBeTrue();

    // Step 2: Encrypt data with initial keys
    $data1 = 'Sensitive data encrypted with initial key';
    $encrypted1 = $this->eloquent_encryption->encrypt($data1);

    // Verify decryption works
    expect($this->eloquent_encryption->decrypt($encrypted1))->toBe($data1);

    // Step 3: Rotate keys (first rotation)
    $this->eloquent_encryption->rotateKeys();

    // Step 4: Verify old data can still be decrypted
    expect($this->eloquent_encryption->decrypt($encrypted1))->toBe($data1);

    // Step 5: Encrypt new data with new keys
    $data2 = 'New sensitive data encrypted with rotated key';
    $encrypted2 = $this->eloquent_encryption->encrypt($data2);

    // Verify new data decryption works
    expect($this->eloquent_encryption->decrypt($encrypted2))->toBe($data2);

    // Verify old data still works
    expect($this->eloquent_encryption->decrypt($encrypted1))->toBe($data1);

    // Step 6: Rotate again (second rotation)
    $this->eloquent_encryption->rotateKeys();

    // Step 7: Verify data encrypted with both old keys can be decrypted
    expect($this->eloquent_encryption->decrypt($encrypted1))->toBe($data1);
    expect($this->eloquent_encryption->decrypt($encrypted2))->toBe($data2);

    // Step 8: Encrypt new data with newest key
    $data3 = 'Latest sensitive data with newest key';
    $encrypted3 = $this->eloquent_encryption->encrypt($data3);

    // Verify all data can be decrypted
    expect($this->eloquent_encryption->decrypt($encrypted1))->toBe($data1)
        ->and($this->eloquent_encryption->decrypt($encrypted2))->toBe($data2)
        ->and($this->eloquent_encryption->decrypt($encrypted3))->toBe($data3);

    // Verify we have the expected number of keys
    $allKeys = $this->eloquent_encryption->getAllKeys();
    expect(count($allKeys))->toBe(3); // Current + 2 previous
});

test('key rotation with serialized data types', function () {
    $this->eloquent_encryption->makeEncryptionKeys();

    // Test with array
    $arrayData = ['name' => 'John', 'age' => 30, 'roles' => ['admin', 'user']];
    $encryptedArray = $this->eloquent_encryption->encrypt($arrayData);

    // Rotate keys
    $this->eloquent_encryption->rotateKeys();

    // Should still decrypt correctly
    expect($this->eloquent_encryption->decrypt($encryptedArray))->toBe($arrayData);

    // Test with object
    $objectData = (object) ['foo' => 'bar', 'baz' => 123];
    $encryptedObject = $this->eloquent_encryption->encrypt($objectData);

    // Rotate again
    $this->eloquent_encryption->rotateKeys();

    // Both should still work
    expect($this->eloquent_encryption->decrypt($encryptedArray))->toBe($arrayData);
    expect($this->eloquent_encryption->decrypt($encryptedObject))->toEqual($objectData);
});

test('key rotation with string encryption', function () {
    $this->eloquent_encryption->makeEncryptionKeys();

    // Encrypt string without serialization
    $plainString = 'Just a plain string without serialization';
    $encryptedString = $this->eloquent_encryption->encryptString($plainString);

    // Rotate keys
    $this->eloquent_encryption->rotateKeys();

    // Should decrypt correctly
    expect($this->eloquent_encryption->decryptString($encryptedString))->toBe($plainString);
});

test('metadata persistence across rotations', function () {
    $this->eloquent_encryption->makeEncryptionKeys();

    // First rotation
    $this->eloquent_encryption->rotateKeys();

    // Check metadata
    Storage::assertExists('.eloquent_encryption_metadata.json');
    $metadata = json_decode(Storage::get('.eloquent_encryption_metadata.json'), true);

    expect($metadata['previous'])->toHaveCount(1)
        ->and($metadata['previous'][0])->toHaveKey('rotated_at');

    // Second rotation
    $this->eloquent_encryption->rotateKeys();

    // Check updated metadata
    $metadata = json_decode(Storage::get('.eloquent_encryption_metadata.json'), true);

    expect($metadata['previous'])->toHaveCount(2)
        ->and($metadata['previous'][0])->toHaveKey('rotated_at')
        ->and($metadata['previous'][1])->toHaveKey('rotated_at');
});

test('max previous keys limit cleanup workflow', function () {
    Config::set('eloquent_encryption.key.max_previous_keys', 2);

    $this->eloquent_encryption->makeEncryptionKeys();

    // Encrypt with key 1
    $data1 = 'Data with key 1';
    $encrypted1 = $this->eloquent_encryption->encrypt($data1);

    // Rotate to key 2, encrypt
    $this->eloquent_encryption->rotateKeys();
    $data2 = 'Data with key 2';
    $encrypted2 = $this->eloquent_encryption->encrypt($data2);

    // Rotate to key 3, encrypt
    $this->eloquent_encryption->rotateKeys();
    $data3 = 'Data with key 3';
    $encrypted3 = $this->eloquent_encryption->encrypt($data3);

    // At this point: Current = key3, Previous = [key1, key2]
    expect($this->eloquent_encryption->decrypt($encrypted1))->toBe($data1)
        ->and($this->eloquent_encryption->decrypt($encrypted2))->toBe($data2)
        ->and($this->eloquent_encryption->decrypt($encrypted3))->toBe($data3);

    // Rotate to key 4 - this should drop key1
    $this->eloquent_encryption->rotateKeys();
    $data4 = 'Data with key 4';
    $encrypted4 = $this->eloquent_encryption->encrypt($data4);

    // At this point: Current = key4, Previous = [key2, key3] (key1 removed)
    expect(count($this->eloquent_encryption->getPreviousKeys()))->toBe(2);

    // Key1 data should fail to decrypt
    expect(fn () => $this->eloquent_encryption->decrypt($encrypted1))
        ->toThrow(Exception::class);

    // But key2, key3, and key4 data should work
    expect($this->eloquent_encryption->decrypt($encrypted2))->toBe($data2)
        ->and($this->eloquent_encryption->decrypt($encrypted3))->toBe($data3)
        ->and($this->eloquent_encryption->decrypt($encrypted4))->toBe($data4);

    // Verify key files were deleted
    Storage::assertMissing('eloquent_encryption.1');
    Storage::assertMissing('eloquent_encryption.1.pub');

    // Verify remaining key files exist
    Storage::assertExists('eloquent_encryption.2');
    Storage::assertExists('eloquent_encryption.2.pub');
    Storage::assertExists('eloquent_encryption.3');
    Storage::assertExists('eloquent_encryption.3.pub');
});

test('empty and null values work across rotation', function () {
    $this->eloquent_encryption->makeEncryptionKeys();

    // Null should return null
    expect($this->eloquent_encryption->decrypt(null))->toBeNull();
    expect($this->eloquent_encryption->decrypt(''))->toBeNull();

    // Rotate and test again
    $this->eloquent_encryption->rotateKeys();

    expect($this->eloquent_encryption->decrypt(null))->toBeNull();
    expect($this->eloquent_encryption->decrypt(''))->toBeNull();
});

test('concurrent encryption with multiple rotations', function () {
    $this->eloquent_encryption->makeEncryptionKeys();

    $testData = [];

    // Create encrypted data, rotating between each encryption
    for ($i = 1; $i <= 5; $i++) {
        $data = "Test data iteration {$i}";
        $encrypted = $this->eloquent_encryption->encrypt($data);

        $testData[] = [
            'plaintext' => $data,
            'encrypted' => $encrypted,
            'iteration' => $i,
        ];

        // Rotate keys between each encryption
        if ($i < 5) {
            $this->eloquent_encryption->rotateKeys();
        }
    }

    // All data should still be decryptable
    foreach ($testData as $item) {
        expect($this->eloquent_encryption->decrypt($item['encrypted']))
            ->toBe($item['plaintext']);
    }
});
