<?php

use Illuminate\Support\Facades\Storage;
use RichardStyles\EloquentEncryption\Exceptions\RSAKeyFileMissing;
use RichardStyles\EloquentEncryption\FileSystem\RsaKeyStorageHandler;

beforeEach(function () {
    Storage::fake();
    $this->handler = new RsaKeyStorageHandler();
});

test('if a public key is missing an error is thrown', function () {
    expect(fn() => $this->handler->getPublicKey())
        ->toThrow(RSAKeyFileMissing::class);
});

test('if a private key is missing an error is thrown', function () {
    expect(fn() => $this->handler->getPrivateKey())
        ->toThrow(RSAKeyFileMissing::class);
});

test('if both key parts are missing exists returns false', function () {
    expect($this->handler->exists())->toBeFalse();
});

test('if public key missing exists returns false', function () {
    $this->makePrivateKey();
    expect($this->handler->exists())->toBeFalse();
});

test('if private key missing exists returns false', function () {
    $this->makePublicKey();
    expect($this->handler->exists())->toBeFalse();
});

test('if public and private keys exists returns true', function () {
    $this->makePublicKey();
    $this->makePrivateKey();
    expect($this->handler->exists())->toBeTrue();
});
