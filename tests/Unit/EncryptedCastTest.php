<?php


namespace RichardStyles\EloquentEncryption\Tests\Unit;


use Illuminate\Foundation\Auth\User;
use RichardStyles\EloquentEncryption\Casts\Encrypted;
use RichardStyles\EloquentEncryption\EloquentEncryptionFacade;
use RichardStyles\EloquentEncryption\Tests\TestCase;

class EncryptedCastTest extends TestCase
{
    /** @test */
    function encrypted_cast_decrypts_values()
    {
        EloquentEncryptionFacade::shouldReceive('exists')
            ->andReturn(true)
            ->shouldReceive('decrypt')
            ->with('001100110011')
            ->andReturn('test');

        $cast = new Encrypted();
        $user = new User();

        $this->assertEquals('test', $cast->get($user, 'encrypted', '001100110011', []));
    }

    /** @test */
    function encrypted_cast_encrypts_values()
    {
        EloquentEncryptionFacade::shouldReceive('exists')
            ->andReturn(true)
            ->shouldReceive('encrypt')
            ->with('test')
            ->andReturn('001100110011');

        $cast = new Encrypted();
        $user = new User();

        $this->assertEquals('001100110011', $cast->set($user, 'encrypted', 'test', []));
    }
}
