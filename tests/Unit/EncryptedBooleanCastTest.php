<?php


namespace RichardStyles\EloquentEncryption\Tests\Unit;


use Illuminate\Foundation\Auth\User;
use RichardStyles\EloquentEncryption\Casts\Encrypted;
use RichardStyles\EloquentEncryption\Casts\EncryptedBoolean;
use RichardStyles\EloquentEncryption\EloquentEncryptionFacade;
use RichardStyles\EloquentEncryption\Tests\TestCase;

class EncryptedBooleanCastTest extends TestCase
{
    /** @test */
    function encrypted_boolean_cast_decrypts_true()
    {
        EloquentEncryptionFacade::shouldReceive('exists')
            ->andReturn(true)
            ->shouldReceive('decryptString')
            ->with('001100110011')
            ->andReturn('1');

        $cast = new EncryptedBoolean();
        $user = new User();

        $response = $cast->get($user, 'encrypted', '001100110011', []);

        $this->assertTrue($response);
    }

    /** @test */
    function encrypted_boolean_cast_decrypts_false()
    {
        EloquentEncryptionFacade::shouldReceive('exists')
            ->andReturn(true)
            ->shouldReceive('decryptString')
            ->with('001100110011')
            ->andReturn('');

        $cast = new EncryptedBoolean();
        $user = new User();

        $response = $cast->get($user, 'encrypted', '001100110011', []);

        $this->assertFalse($response);
    }

    /** @test */
    function encrypted_boolean_cast_encrypts_values()
    {
        EloquentEncryptionFacade::partialMock()
            ->shouldReceive('exists')
            ->andReturn(true)
            ->shouldReceive('encryptString')
            ->with(true)
            ->andReturn('001100110011');

        $cast = new EncryptedBoolean();
        $user = new User();

        $this->assertEquals('001100110011', $cast->set($user, 'encrypted', 110011001100, []));
    }
}
