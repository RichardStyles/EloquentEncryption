<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use RichardStyles\EloquentEncryption\EloquentEncryptionFacade;

beforeEach(function () {
    Storage::fake();
});

test('the command encrypt:generate creates rsa key files', function () {
    $this->artisan('encrypt:generate')
        ->expectsOutput('Creating RSA Keys for Application')
        ->assertExitCode(0);

    expect(EloquentEncryptionFacade::exists())->toBeTrue();
});

test('if rsa keys exist then show warning', function () {
    $this->artisan('encrypt:generate')
        ->expectsOutput('Creating RSA Keys for Application')
        ->assertExitCode(0);

    expect(EloquentEncryptionFacade::exists())->toBeTrue();

    $this->artisan('encrypt:generate')
        ->expectsOutput('Application RSA keys are already set')
        ->expectsOutput('**********************************************************************')
        ->expectsOutput('* If you reset your keys you will lose access to any encrypted data. *')
        ->expectsOutput('**********************************************************************')
        ->expectsQuestion('Do you wish to reset your encryption keys?', 'no')
        ->assertExitCode(0);
});

test('if rsa keys can be overwritten', function () {
    $this->artisan('encrypt:generate')
        ->expectsOutput('Creating RSA Keys for Application')
        ->assertExitCode(0);

    expect(EloquentEncryptionFacade::exists())->toBeTrue();

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

    expect($public)->not->toBe($original_public);
    expect($private)->not->toBe($original_private);
});
