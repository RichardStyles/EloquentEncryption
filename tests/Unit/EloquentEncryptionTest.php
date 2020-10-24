<?php

namespace RichardStyles\EloquentEncryption\Tests\Unit;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use phpseclib\Crypt\RSA;
use RichardStyles\EloquentEncryption\EloquentEncryption;
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


}
