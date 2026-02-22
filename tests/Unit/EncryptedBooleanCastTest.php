<?php

use Illuminate\Foundation\Auth\User;
use RichardStyles\EloquentEncryption\Casts\EncryptedBoolean;
use RichardStyles\EloquentEncryption\EloquentEncryptionFacade;

test('encrypted boolean cast decrypts true', function () {
    EloquentEncryptionFacade::shouldReceive('exists')
        ->andReturn(true)
        ->shouldReceive('decryptString')
        ->with('001100110011')
        ->andReturn('1');

    $cast = new EncryptedBoolean;
    $user = new User;

    $response = $cast->get($user, 'encrypted', '001100110011', []);

    expect($response)->toBeTrue();
});

test('encrypted boolean cast decrypts false', function () {
    EloquentEncryptionFacade::shouldReceive('exists')
        ->andReturn(true)
        ->shouldReceive('decryptString')
        ->with('001100110011')
        ->andReturn('');

    $cast = new EncryptedBoolean;
    $user = new User;

    $response = $cast->get($user, 'encrypted', '001100110011', []);

    expect($response)->toBeFalse();
});

test('encrypted boolean cast encrypts values', function () {
    EloquentEncryptionFacade::partialMock()
        ->shouldReceive('exists')
        ->andReturn(true)
        ->shouldReceive('encryptString')
        ->with(true)
        ->andReturn('001100110011');

    $cast = new EncryptedBoolean;
    $user = new User;

    expect($cast->set($user, 'encrypted', 110011001100, []))->toBe('001100110011');
});
