<?php

use Illuminate\Foundation\Auth\User;
use RichardStyles\EloquentEncryption\Casts\EncryptedFloat;
use RichardStyles\EloquentEncryption\EloquentEncryptionFacade;

test('encrypted float cast decrypts values', function () {
    EloquentEncryptionFacade::shouldReceive('exists')
        ->andReturn(true)
        ->shouldReceive('decryptString')
        ->with('001100110011')
        ->andReturn('1.245');

    $cast = new EncryptedFloat();
    $user = new User();

    $response = $cast->get($user, 'encrypted', '001100110011', []);

    expect($response)->toBeFloat();
    expect($response)->toBe(1.245);
});

test('encrypted float cast encrypts values', function () {
    EloquentEncryptionFacade::partialMock()
        ->shouldReceive('exists')
        ->andReturn(true)
        ->shouldReceive('encryptString')
        ->with(1.245)
        ->andReturn('001100110011');

    $cast = new EncryptedFloat();
    $user = new User();

    expect($cast->set($user, 'encrypted', 1.245, []))->toBe('001100110011');
});

test('decrypting inf float', function () {
    EloquentEncryptionFacade::shouldReceive('exists')
        ->andReturn(true)
        ->shouldReceive('decryptString')
        ->with('001100110011')
        ->andReturn(INF);

    $cast = new EncryptedFloat();
    $user = new User();

    $response = $cast->get($user, 'encrypted', '001100110011', []);

    expect($response)->toBe(INF);
});
