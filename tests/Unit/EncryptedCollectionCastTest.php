<?php


namespace RichardStyles\EloquentEncryption\Tests\Unit;


use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection;
use RichardStyles\EloquentEncryption\Casts\EncryptedCollection;
use RichardStyles\EloquentEncryption\EloquentEncryptionFacade;
use RichardStyles\EloquentEncryption\Tests\TestCase;

class EncryptedCollectionCastTest extends TestCase
{
    /** @test */
    function encrypted_collection_cast_decrypts_values()
    {
        EloquentEncryptionFacade::shouldReceive('exists')
            ->andReturn(true)
            ->shouldReceive('decryptString')
            ->with('001100110011')
            ->shouldReceive('decrypt')
            ->with('001100110011')
            ->andReturn('{"test":"a","foo":"bar","bar":{"test":"result"}}');

        $cast = new EncryptedCollection();
        $user = new User();

        $response = $cast->get($user, 'encrypted', '001100110011', []);

        $this->assertInstanceOf(Collection::class, $response);
    }

    /** @test */
    function encrypted_collection_cast_encrypts_values()
    {
        $collect = collect([
            'test' => 'a',
            'foo'  => 'bar',
            'bar'  => [
                'test' => 'result'
            ]
        ]);
        EloquentEncryptionFacade::partialMock()
            ->shouldReceive('exists')
            ->andReturn(true)
            ->shouldReceive('encryptString')
            ->with('{"test":"a","foo":"bar","bar":{"test":"result"}}')
            ->andReturn('001100110011');

        $cast = new EncryptedCollection();
        $user = new User();

        $this->assertEquals('001100110011', $cast->set($user, 'encrypted', $collect, []));
    }

    /** @test */
    function cannot_encrypted_invalid_array_values()
    {
        $cast = new EncryptedCollection();
        $user = new User();

        $this->expectException(JsonEncodingException::class);
        $this->assertEquals('001100110011', $cast->set($user, 'encrypted', "\xB1\x31", []));
    }
}
