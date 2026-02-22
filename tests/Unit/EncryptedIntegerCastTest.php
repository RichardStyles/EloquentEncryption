<?php

use Illuminate\Foundation\Auth\User;
use RichardStyles\EloquentEncryption\Casts\EncryptedInteger;
use RichardStyles\EloquentEncryption\EloquentEncryptionFacade;

test('encrypted integer cast decrypts values', function () {
    EloquentEncryptionFacade::shouldReceive('exists')
        ->andReturn(true)
        ->shouldReceive('decryptString')
        ->with('001100110011')
        ->andReturn('001100110011');

    $cast = new EncryptedInteger();
    $user = new User();

    $response = $cast->get($user, 'encrypted', '001100110011', []);

    expect($response)->toBeInt();
    expect($response)->toBe(1100110011);
});

test('encrypted integer cast encrypts values', function () {
    EloquentEncryptionFacade::partialMock()
        ->shouldReceive('exists')
        ->andReturn(true)
        ->shouldReceive('encryptString')
        ->with(110011001100)
        ->andReturn('001100110011');

    $cast = new EncryptedInteger();
    $user = new User();

    expect($cast->set($user, 'encrypted', 110011001100, []))->toBe('001100110011');
});
