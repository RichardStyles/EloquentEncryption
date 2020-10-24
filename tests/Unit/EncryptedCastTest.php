<?php


namespace RichardStyles\EloquentEncryption\Tests\Unit;


use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Storage;
use RichardStyles\EloquentEncryption\Casts\Encrypted;
use RichardStyles\EloquentEncryption\EloquentEncryptionFacade;
use RichardStyles\EloquentEncryption\Tests\TestCase;
use RichardStyles\EloquentEncryption\Tests\Traits\WithRSAHelpers;

class EncryptedCastTest extends TestCase
{
    use WithRSAHelpers;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake();
    }

    /** @test */
    function encrypted_cast_decrypts_values()
    {
        EloquentEncryptionFacade::partialMock()
            ->shouldReceive('exists')
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
        EloquentEncryptionFacade::partialMock()
            ->shouldReceive('exists')
            ->andReturn(true)
            ->shouldReceive('encrypt')
            ->with('test')
            ->andReturn('001100110011');

        $cast = new Encrypted();

        $user = new User();

        $this->assertEquals('001100110011', $cast->set($user, 'encrypted', 'test', []));
    }
}
