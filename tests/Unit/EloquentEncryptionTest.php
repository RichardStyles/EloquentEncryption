<?php

namespace RichardStyles\EloquentEncryption\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use RichardStyles\EloquentEncryption\EloquentEncryption;
use RichardStyles\EloquentEncryption\Exceptions\InvalidRsaKeyHandler;
use RichardStyles\EloquentEncryption\Exceptions\RSAKeyFileMissing;
use RichardStyles\EloquentEncryption\Tests\TestCase;
use RichardStyles\EloquentEncryption\Tests\Traits\WithRSAHelpers;

class EloquentEncryptionTest extends TestCase
{
     use WithRSAHelpers;

    /**
     * @var EloquentEncryption
     */
    private $eloquent_encryption;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake();

        $this->eloquent_encryption = new EloquentEncryption();
    }

    /** @test */
    function if_a_public_key_is_missing_an_error_is_thrown()
    {
        $this->expectException(RSAKeyFileMissing::class);
        $this->expectExceptionObject(new RSAKeyFileMissing);
        $this->eloquent_encryption->getPublicKey();
    }

    /** @test */
    function if_a_private_key_is_missing_an_error_is_thrown()
    {
        $this->expectException(RSAKeyFileMissing::class);
        $this->expectExceptionObject(new RSAKeyFileMissing);
        $this->eloquent_encryption->getPrivateKey();
    }

    /** @test */
    function if_both_key_parts_are_missing_exists_returns_false()
    {
        $this->assertFalse($this->eloquent_encryption->exists());
    }

    /** @test */
    function if_public_key_missing_exists_returns_false()
    {
        $this->makePrivateKey();

        $this->assertFalse($this->eloquent_encryption->exists());
    }

    /** @test */
    function if_private_key_missing_exists_returns_false()
    {
        $this->makePublicKey();

        $this->assertFalse($this->eloquent_encryption->exists());
    }

    /** @test */
    function if_public_and_private_keys_exists_returns_true()
    {
        $this->makePublicKey();
        $this->makePrivateKey();

        $this->assertTrue($this->eloquent_encryption->exists());
    }

    /** @test */
    function a_valid_rsa_key_pair_is_created()
    {
        $this->eloquent_encryption->makeEncryptionKeys();

        $this->validateRSAKeys(
            $this->eloquent_encryption->getPublicKey(),
            $this->eloquent_encryption->getPrivateKey()
        );
    }

    /** @test */
    function a_string_can_be_encrypted_and_decrypted()
    {
        $this->eloquent_encryption->makeEncryptionKeys();

        $this->assertTrue($this->eloquent_encryption->exists());

        $toEncrypt = $this->faker->paragraph;

        $encrypted = $this->eloquent_encryption->encrypt($toEncrypt);
        $decrypted = $this->eloquent_encryption->decrypt($encrypted);

        $this->assertEquals($toEncrypt, $decrypted);
    }

    /** @test */
    function an_invalid_rsa_key_handler_throws_exception()
    {
        Config::set('eloquent_encryption.handler', BadRsaKeyHandler::class);

        $this->expectException(InvalidRsaKeyHandler::class);

        $eloquent_encryption = new EloquentEncryption();
    }

    /** @test */
    function get_key_returns_the_private_key()
    {
        $this->makePublicKey();
        $this->makeRawKey('','eloquent_encryption','Super secret key');

        $this->assertEquals('Super secret key', $this->eloquent_encryption->getKey());
    }
}

class BadRsaKeyHandler
{
    public $foo;
}
