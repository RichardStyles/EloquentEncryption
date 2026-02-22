<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use RichardStyles\EloquentEncryption\EloquentEncryption;
use RichardStyles\EloquentEncryption\Exceptions\InvalidRsaKeyHandler;
use RichardStyles\EloquentEncryption\Exceptions\RSAKeyFileMissing;

beforeEach(function () {
    Storage::fake();
    $this->eloquent_encryption = new EloquentEncryption();
});

test('if a public key is missing an error is thrown', function () {
    expect(fn() => $this->eloquent_encryption->getPublicKey())
        ->toThrow(RSAKeyFileMissing::class);
});

test('if a private key is missing an error is thrown', function () {
    expect(fn() => $this->eloquent_encryption->getPrivateKey())
        ->toThrow(RSAKeyFileMissing::class);
});

test('if both key parts are missing exists returns false', function () {
    expect($this->eloquent_encryption->exists())->toBeFalse();
});

test('if public key missing exists returns false', function () {
    $this->makePrivateKey();
    expect($this->eloquent_encryption->exists())->toBeFalse();
});

test('if private key missing exists returns false', function () {
    $this->makePublicKey();
    expect($this->eloquent_encryption->exists())->toBeFalse();
});

test('if public and private keys exists returns true', function () {
    $this->makePublicKey();
    $this->makePrivateKey();
    expect($this->eloquent_encryption->exists())->toBeTrue();
});

test('a valid rsa key pair is created', function () {
    $this->eloquent_encryption->makeEncryptionKeys();

    $this->validateRSAKeys(
        $this->eloquent_encryption->getPublicKey(),
        $this->eloquent_encryption->getPrivateKey()
    );
});

test('a string can be encrypted and decrypted', function () {
    $this->eloquent_encryption->makeEncryptionKeys();

    expect($this->eloquent_encryption->exists())->toBeTrue();

    $toEncrypt = $this->faker->paragraph;

    $encrypted = $this->eloquent_encryption->encrypt($toEncrypt);
    $decrypted = $this->eloquent_encryption->decrypt($encrypted);

    expect($decrypted)->toBe($toEncrypt);
});

test('an invalid rsa key handler throws exception', function () {
    Config::set('eloquent_encryption.handler', BadRsaKeyHandler::class);

    expect(fn() => new EloquentEncryption())
        ->toThrow(InvalidRsaKeyHandler::class);
});

test('get key returns the private key', function () {
    $this->makePublicKey();
    $this->makeRawKey('', 'eloquent_encryption', 'Super secret key');

    expect($this->eloquent_encryption->getKey())->toBe('Super secret key');
});

class BadRsaKeyHandler
{
    public $foo;
}
