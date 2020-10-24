<?php


namespace RichardStyles\EloquentEncryption\Tests\Unit;


use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use RichardStyles\EloquentEncryption\EloquentEncryptionFacade;
use RichardStyles\EloquentEncryption\Tests\TestCase;
use RichardStyles\EloquentEncryption\Tests\Traits\WithRSAHelpers;

class GenerateRsaKeysCommandTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake();
    }

    /** @test */
    function the_command_encrypt_generate_creates_rsa_key_files()
    {
        $this->artisan('encrypt:generate')
            ->expectsOutput('Creating RSA Keys for Application')
            ->assertExitCode(0);

        $this->assertTrue(EloquentEncryptionFacade::exists());
    }

    /** @test */
    function if_rsa_keys_exist_then_show_warning()
    {
        $this->artisan('encrypt:generate')
            ->expectsOutput('Creating RSA Keys for Application')
            ->assertExitCode(0);

        $this->assertTrue(EloquentEncryptionFacade::exists());

        $this->artisan('encrypt:generate')
            ->expectsOutput('Application RSA keys are already set')
            ->expectsOutput('**********************************************************************')
            ->expectsOutput('* If you reset your keys you will lose access to any encrypted data. *')
            ->expectsOutput('**********************************************************************')
            ->expectsQuestion('Do you wish to reset your encryption keys?', 'no')
            ->expectsOutput('RSA Keys have not been overwritted')
            ->assertExitCode(0);
    }

    /** @test */
    function if_rsa_keys_can_be_overwritten()
    {
        $this->artisan('encrypt:generate')
            ->expectsOutput('Creating RSA Keys for Application')
            ->assertExitCode(0);

        $this->assertTrue(EloquentEncryptionFacade::exists());

        $original_public = Storage::get(Config::get('eloquent_encryption.key.public', 'eloquent_encryption.pub'));
        $original_private = Storage::get(Config::get('eloquent_encryption.key.public', 'eloquent_encryption'));

        $this->artisan('encrypt:generate')
            ->expectsOutput('Application RSA keys are already set')
            ->expectsOutput('**********************************************************************')
            ->expectsOutput('* If you reset your keys you will lose access to any encrypted data. *')
            ->expectsOutput('**********************************************************************')
            ->expectsQuestion('Do you wish to reset your encryption keys?', 'yes')
            ->expectsOutput('Creating RSA Keys for Application')
            ->assertExitCode(0);

        $public = Storage::get(Config::get('eloquent_encryption.key.public', 'eloquent_encryption.pub'));
        $private = Storage::get(Config::get('eloquent_encryption.key.public', 'eloquent_encryption'));

        $this->assertNotEquals($original_public, $public);
        $this->assertNotEquals($original_private, $private);
    }
}
