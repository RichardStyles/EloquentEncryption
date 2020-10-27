<?php


namespace RichardStyles\EloquentEncryption\Tests\Unit;

use Illuminate\Foundation\Auth\User;
use RichardStyles\EloquentEncryption\Casts\EncryptedFloat;
use RichardStyles\EloquentEncryption\EloquentEncryptionFacade;
use RichardStyles\EloquentEncryption\Tests\TestCase;

class EncryptedFloatCastTest extends TestCase
{
    /** @test */
    function encrypted_float_cast_decrypts_values()
    {
        EloquentEncryptionFacade::shouldReceive('exists')
            ->andReturn(true)
            ->shouldReceive('decrypt')
            ->with('001100110011')
            ->andReturn('1.245');

        $cast = new EncryptedFloat();
        $user = new User();

        $response = $cast->get($user, 'encrypted', '001100110011', []);

        $this->assertIsFloat($response);
        $this->assertEquals(1.245, $response);
    }

    /** @test */
    function encrypted_float_cast_encrypts_values()
    {
        EloquentEncryptionFacade::partialMock()
            ->shouldReceive('exists')
            ->andReturn(true)
            ->shouldReceive('encrypt')
            ->with(1.245)
            ->andReturn('001100110011');

        $cast = new EncryptedFloat();
        $user = new User();

        $this->assertEquals('001100110011', $cast->set($user, 'encrypted', 1.245, []));
    }

    /** @test */
    function decrypting_inf_float()
    {
        EloquentEncryptionFacade::shouldReceive('exists')
            ->andReturn(true)
            ->shouldReceive('decrypt')
            ->with('001100110011')
            ->andReturn(INF);

        $cast = new EncryptedFloat();
        $user = new User();

        $response = $cast->get($user, 'encrypted', '001100110011', []);

        $this->assertEquals(INF, $response);
    }

}
