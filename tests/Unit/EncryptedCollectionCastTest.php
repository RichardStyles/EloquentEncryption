<?php

use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection;
use RichardStyles\EloquentEncryption\Casts\EncryptedCollection;
use RichardStyles\EloquentEncryption\EloquentEncryptionFacade;

test('encrypted collection cast decrypts values', function () {
    EloquentEncryptionFacade::shouldReceive('exists')
        ->andReturn(true)
        ->shouldReceive('decryptString')
        ->with('001100110011')
        ->shouldReceive('decrypt')
        ->with('001100110011')
        ->andReturn('{"test":"a","foo":"bar","bar":{"test":"result"}}');

    $cast = new EncryptedCollection;
    $user = new User;

    $response = $cast->get($user, 'encrypted', '001100110011', []);

    expect($response)->toBeInstanceOf(Collection::class);
});

test('encrypted collection cast encrypts values', function () {
    $collect = collect([
        'test' => 'a',
        'foo' => 'bar',
        'bar' => [
            'test' => 'result',
        ],
    ]);
    EloquentEncryptionFacade::partialMock()
        ->shouldReceive('exists')
        ->andReturn(true)
        ->shouldReceive('encryptString')
        ->with('{"test":"a","foo":"bar","bar":{"test":"result"}}')
        ->andReturn('001100110011');

    $cast = new EncryptedCollection;
    $user = new User;

    expect($cast->set($user, 'encrypted', $collect, []))->toBe('001100110011');
});

test('cannot encrypted invalid array values', function () {
    $cast = new EncryptedCollection;
    $user = new User;

    expect(fn () => $cast->set($user, 'encrypted', "\xB1\x31", []))
        ->toThrow(JsonEncodingException::class);
});

test('encrypted collection cast handles null values', function () {
    $cast = new EncryptedCollection;
    $user = new User;

    $response = $cast->get($user, 'encrypted', null, []);

    expect($response)->toBeInstanceOf(Collection::class)
        ->and($response)->toBeEmpty();
});
