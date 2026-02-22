<?php

use Illuminate\Foundation\Auth\User;
use RichardStyles\EloquentEncryption\Casts\Encrypted;
use RichardStyles\EloquentEncryption\EloquentEncryptionFacade;

test('encrypted cast decrypts values', function () {
    EloquentEncryptionFacade::shouldReceive('exists')
        ->andReturn(true)
        ->shouldReceive('decryptString')
        ->with('001100110011')
        ->andReturn('test');

    $cast = new Encrypted;
    $user = new User;

    expect($cast->get($user, 'encrypted', '001100110011', []))->toBe('test');
});

test('encrypted cast encrypts values', function () {
    EloquentEncryptionFacade::shouldReceive('exists')
        ->andReturn(true)
        ->shouldReceive('encryptString')
        ->with('test')
        ->andReturn('001100110011');

    $cast = new Encrypted;
    $user = new User;

    expect($cast->set($user, 'encrypted', 'test', []))->toBe('001100110011');
});
