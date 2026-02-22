<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use RichardStyles\EloquentEncryption\EloquentEncryptionFacade;

beforeEach(function () {
    Storage::fake();
});

test('command fails when no keys exist', function () {
    $this->artisan('encrypt:rotate')
        ->expectsOutput('No encryption keys found. Please run encrypt:generate first.')
        ->assertExitCode(1);
});

test('command prompts for confirmation', function () {
    // Generate initial keys
    EloquentEncryptionFacade::makeEncryptionKeys();

    $this->artisan('encrypt:rotate')
        ->expectsOutput('This will generate new RSA keys and move the current keys to the previous keys list.')
        ->expectsOutput('Data encrypted with old keys will still be decryptable.')
        ->expectsQuestion('Do you wish to rotate your encryption keys?', false)
        ->expectsOutput('Key rotation cancelled.')
        ->assertExitCode(0);
});

test('command rotates keys on confirmation', function () {
    // Generate initial keys
    EloquentEncryptionFacade::makeEncryptionKeys();

    $originalPublic = Storage::get(Config::get('eloquent_encryption.key.public', 'eloquent_encryption.pub'));
    $originalPrivate = Storage::get(Config::get('eloquent_encryption.key.private', 'eloquent_encryption'));

    $this->artisan('encrypt:rotate')
        ->expectsOutput('This will generate new RSA keys and move the current keys to the previous keys list.')
        ->expectsOutput('Data encrypted with old keys will still be decryptable.')
        ->expectsQuestion('Do you wish to rotate your encryption keys?', true)
        ->expectsOutput('Rotating RSA keys...')
        ->expectsOutput('✓ RSA keys rotated successfully!')
        ->expectsOutput('New keys are now active. Previous keys are maintained for decryption.')
        ->assertExitCode(0);

    // Verify keys were rotated
    $newPublic = Storage::get(Config::get('eloquent_encryption.key.public', 'eloquent_encryption.pub'));
    $newPrivate = Storage::get(Config::get('eloquent_encryption.key.private', 'eloquent_encryption'));

    expect($newPublic)->not->toBe($originalPublic);
    expect($newPrivate)->not->toBe($originalPrivate);

    // Verify previous keys were saved
    Storage::assertExists('eloquent_encryption.1');
    Storage::assertExists('eloquent_encryption.1.pub');
});

test('command cancels on rejection', function () {
    // Generate initial keys
    EloquentEncryptionFacade::makeEncryptionKeys();

    $originalPublic = Storage::get(Config::get('eloquent_encryption.key.public', 'eloquent_encryption.pub'));
    $originalPrivate = Storage::get(Config::get('eloquent_encryption.key.private', 'eloquent_encryption'));

    $this->artisan('encrypt:rotate')
        ->expectsQuestion('Do you wish to rotate your encryption keys?', false)
        ->expectsOutput('Key rotation cancelled.')
        ->assertExitCode(0);

    // Verify keys were NOT changed
    $currentPublic = Storage::get(Config::get('eloquent_encryption.key.public', 'eloquent_encryption.pub'));
    $currentPrivate = Storage::get(Config::get('eloquent_encryption.key.private', 'eloquent_encryption'));

    expect($currentPublic)->toBe($originalPublic);
    expect($currentPrivate)->toBe($originalPrivate);

    // Verify no previous keys were created
    Storage::assertMissing('eloquent_encryption.1');
    Storage::assertMissing('eloquent_encryption.1.pub');
});

test('command displays correct output messages', function () {
    EloquentEncryptionFacade::makeEncryptionKeys();

    $this->artisan('encrypt:rotate')
        ->expectsOutput('This will generate new RSA keys and move the current keys to the previous keys list.')
        ->expectsOutput('Data encrypted with old keys will still be decryptable.')
        ->expectsQuestion('Do you wish to rotate your encryption keys?', true)
        ->expectsOutput('Rotating RSA keys...')
        ->expectsOutput('✓ RSA keys rotated successfully!')
        ->expectsOutput('New keys are now active. Previous keys are maintained for decryption.')
        ->assertExitCode(0);
});

test('command can be run multiple times', function () {
    EloquentEncryptionFacade::makeEncryptionKeys();

    // First rotation
    $this->artisan('encrypt:rotate')
        ->expectsQuestion('Do you wish to rotate your encryption keys?', true)
        ->assertExitCode(0);

    Storage::assertExists('eloquent_encryption.1');

    // Second rotation
    $this->artisan('encrypt:rotate')
        ->expectsQuestion('Do you wish to rotate your encryption keys?', true)
        ->assertExitCode(0);

    Storage::assertExists('eloquent_encryption.1');
    Storage::assertExists('eloquent_encryption.2');

    // Third rotation
    $this->artisan('encrypt:rotate')
        ->expectsQuestion('Do you wish to rotate your encryption keys?', true)
        ->assertExitCode(0);

    Storage::assertExists('eloquent_encryption.1');
    Storage::assertExists('eloquent_encryption.2');
    Storage::assertExists('eloquent_encryption.3');
});
