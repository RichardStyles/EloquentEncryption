# Eloquent Encryption

This package enables an additional layer of security when handling sensitive data. Allowing key fields of your eloquent models in the database to be encrypted at rest.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/richardstyles/eloquentencryption.svg?style=flat-square)](https://packagist.org/packages/richardstyles/eloquentencryption)
[![Build Status](https://img.shields.io/travis/richardstyles/eloquentencryption/master.svg?style=flat-square)](https://travis-ci.org/richardstyles/eloquentencryption)
[![Quality Score](https://img.shields.io/scrutinizer/g/richardstyles/eloquentencryption.svg?style=flat-square)](https://scrutinizer-ci.com/g/richardstyles/eloquentencryption)
[![Total Downloads](https://img.shields.io/packagist/dt/richardstyles/eloquentencryption.svg?style=flat-square)](https://packagist.org/packages/richardstyles/eloquentencryption)

> **🎉 Version 4.x is now available!**
>
> **Requirements:** Laravel 12-13 | PHP 8.2+ | phpseclib v3
>
> **⚠️ Breaking Change:** This version requires Laravel 12+ and PHP 8.2+. For older versions, use [v3.x](https://github.com/RichardStyles/EloquentEncryption/tree/3.x).
>
> [See upgrade guide](#upgrading) for migration instructions.

## Introduction

This open source package fulfils the need of encrypting selected model data in your database whilst allowing your app:key to be rotated. When needing to store private details this package allows for greater security than the default Laravel encrypter.

The package supports two encryption methods:
- **RSA Encryption**: Uses 4096-bit asymmetric keys providing robust security for encrypting sensitive data fields with public-key cryptography.
- **X25519 Encryption**: Leverages modern Curve25519 elliptic curve cryptography for faster performance while maintaining strong security guarantees.

Both methods use Laravel model casting to dynamically encrypt and decrypt key fields.

Usually, you would use [Laravel's Encrypter](https://laravel.com/docs/12.x/encryption) to encrypt the data, but this has the limitation of using the `app:key` as the private secret. As the app key also secures session/cookie data, it is [advised that you rotate this every so often](https://tighten.co/blog/app-key-and-you/) - if you're storing encrypted data using this method you have to decrypt it all first and re-encrypt whenever this is done. Therefore this package improves on this by creating a separate and stronger encryption process allowing you to rotate the app:key. This allows for a level of security of sensitive model data within your Laravel application and your database.

If you don't want to use RSA keys, then I have another package [Eloquent AES](https://github.com/RichardStyles/eloquent-aes) which uses a separate key `eloquent_key` to encrypt using AES-256-CBC.

## Requirements

### Version 4.x (Current)

| Requirement | Version |
|------------|---------|
| **PHP** | 8.2, 8.3, 8.4, or 8.5 |
| **Laravel** | 12.x or 13.x |
| **phpseclib** | v3.0+ |

### Older Laravel/PHP Versions?

If you're using an older version of Laravel or PHP, use version 3.x instead:

```bash
composer require richardstyles/eloquentencryption:^3.0
```

**Version 3.x supports:**
- Laravel 8.x, 9.x, 10.x, 11.x
- PHP 8.0, 8.1, 8.2, 8.3

## Installation

Install the package via composer:

```bash
composer require richardstyles/eloquentencryption
```

You do not need to register the ServiceProvider as this package uses Laravel Package auto discovery.
The Migration blueprint helpers are added using macros, so do not affect the schema files.

The configuration can be published using this command, if you need to change the RSA key size, storage path and key file names.

```bash
php artisan vendor:publish --provider="RichardStyles\EloquentEncryption\EloquentEncryptionServiceProvider" --tag="config"
```

In order to encrypt and decrypt data you need to generate RSA keys for this package. By default, this will create 4096-bit RSA keys to your `storage/` directory. **Do not add these to version control** and backup accordingly.

```bash
php artisan encrypt:generate
```

### ⚠️  **If you re-run this command, you will lose access to any encrypted data** ⚠️

## Quick Start Checklist

After installation, follow these steps to get started:

1. ✅ **Generate RSA Keys**: Run `php artisan encrypt:generate`
2. ✅ **Configure Model Encryption**: Add `Model::encryptUsing(new EloquentEncryption())` to `AppServiceProvider::boot()` (see [Usage](#usage))
3. ✅ **Add Encrypted Columns**: Use the `$table->encrypted('field_name')` helper in migrations
4. ✅ **Cast Model Attributes**: Add `'field_name' => 'encrypted'` to your model's `$casts` array
5. ✅ **Backup Your Keys**: Ensure RSA keys in `storage/` are backed up securely and excluded from version control

---

## Migration Helpers

There is a helper function to define your encrypted fields in your migrations.
There is nothing special needed for this to function, simply declare a `encrypted` column type in your migration files. This just creates a `binary`/`blob` column to hold the encrypted data. Using this helper indicates that the field is encrypted when looking through your migrations.

```php
Schema::create('sales_notes', function (Blueprint $table) {
    $table->increments('id');
    $table->encrypted('private_data');
    $table->encrypted('optional_private_data')->nullable();
    $table->timestamps();
});
```

You can use any additional blueprint helpers, such as `->nullable()` if there is no initial data to encrypt. It is advised that `->index()` shouldn't normally be placed on these binary fields as you should not be querying against these, given they are encrypted.

## Usage

### Step 1: Configure the Encrypter (Required)

Laravel provides the `Model::encryptUsing()` static method on the base Eloquent Model. This allows the built-in encrypted casting to use any `Illuminate\Contracts\Encryption\Encrypter` implementation - including this package's RSA encryption.

Add the following to your `App\Providers\AppServiceProvider.php` in the `boot()` method:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use RichardStyles\EloquentEncryption\EloquentEncryption;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure all models to use RSA encryption for encrypted casts
        Model::encryptUsing(new EloquentEncryption());
    }
}
```

**Important:** This must be configured before any models with encrypted casts are instantiated. The `AppServiceProvider::boot()` method is the ideal location.

### Step 2: Use Encrypted Casts in Your Models

Once configured, you can use Laravel's built-in encrypted casts on any model:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'ssn' => 'encrypted',                      // String
        'preferences' => 'encrypted:array',        // Array
        'metadata' => 'encrypted:json',            // JSON
        'settings' => 'encrypted:object',          // Object
        'tags' => 'encrypted:collection',          // Collection
    ];
}
```

### Accessing Encrypted Data

Once configured, your encrypted attributes work seamlessly with no additional code:

```php
// Create a user with encrypted data
$user = User::create([
    'name' => 'John Doe',
    'ssn' => '123-45-6789',              // Automatically encrypted when saved
    'preferences' => ['theme' => 'dark'], // Automatically encrypted as JSON
]);

// Access decrypted data (automatic)
echo $user->ssn;                 // '123-45-6789' (decrypted automatically)
echo $user->preferences['theme']; // 'dark' (decrypted automatically)

// Update encrypted data
$user->ssn = '987-65-4321';      // Will be encrypted when saved
$user->save();
```

### Database Storage

In your database, the encrypted fields are stored as binary data:

```php
// Raw database value (binary encrypted data)
DB::table('users')->value('ssn'); // Returns encrypted binary data

// Through the model (automatic decryption)
User::find(1)->ssn; // Returns decrypted '123-45-6789'
```

This was made possible by a [PR to Laravel](https://github.com/laravel/framework/pull/35080) by [@hivokas](https://github.com/hivokas).

---

## Key Rotation

For enhanced security, you can rotate your RSA encryption keys periodically. The package supports key rotation without losing access to previously encrypted data.

### How Key Rotation Works

1. **Generate new keys**: Creates a new RSA key pair
2. **Preserve old keys**: Moves current keys to a "previous keys" list
3. **Decrypt old data**: Data encrypted with previous keys can still be decrypted
4. **Encrypt new data**: New data is encrypted with the latest key

### Rotating Keys

```bash
php artisan encrypt:rotate
```

This command will:
- Generate a new 4096-bit RSA key pair
- Move your current keys to the previous keys list
- Maintain up to 5 previous key pairs (configurable)
- Preserve the ability to decrypt data encrypted with any previous key

### Configuration

You can configure the maximum number of previous keys to maintain:

```php
// config/eloquent_encryption.php
'key' => [
    'max_previous_keys' => 5, // Keep up to 5 previous key pairs
],
```

### Key Storage Structure (Default File-Based Handler)

When using the default `RsaKeyStorageHandler`, key rotation history is tracked in a metadata file. Each previous key pair includes:

- **Public Key Path**: Path to the public key file
- **Private Key Path**: Path to the private key file
- **Rotation Timestamp**: ISO 8601 timestamp of when the key was rotated

The metadata is stored in `storage/.eloquent_encryption_metadata.json`:

```json
{
  "current": {
    "public": "eloquent_encryption.pub",
    "private": "eloquent_encryption"
  },
  "previous": [
    {
      "public": "eloquent_encryption.1.pub",
      "private": "eloquent_encryption.1",
      "rotated_at": "2026-01-15T10:30:00+00:00"
    },
    {
      "public": "eloquent_encryption.2.pub",
      "private": "eloquent_encryption.2",
      "rotated_at": "2026-02-22T14:45:00+00:00"
    }
  ]
}
```

**Important Notes:**
- **Previous public keys are maintained for audit trails** but are not used for cryptographic operations
- Only **previous private keys** are used when decrypting data encrypted with rotated keys
- Public and private keys are kept together as pairs to maintain historical integrity
- Each rotation is timestamped for compliance and security auditing
- When the `max_previous_keys` limit is reached, the oldest key pair is removed entirely

> **Note:** This metadata structure is specific to the default `RsaKeyStorageHandler`. If you implement a [custom key storage handler](#custom-key-storage-handlers), you can manage key rotation history however you prefer, as long as your `getPreviousKeys()` method returns the required structured format.

### Security Best Practices

- **Regular rotation**: Rotate keys every 6-12 months
- **Backup before rotation**: Always backup your current keys before rotating
- **Monitor access**: Track which keys are being used for decryption
- **Cleanup old keys**: After re-encrypting all data, remove very old previous keys

### Re-encrypting Data with New Keys

After rotation, existing data remains encrypted with old keys. To re-encrypt with the new key:

1. Read the encrypted attribute (triggers decryption with previous key)
2. Save the model (triggers encryption with new current key)

Example:
```php
// This will decrypt with old key and re-encrypt with new key
User::chunk(100, function ($users) {
    foreach ($users as $user) {
        if ($user->highly_sensitive_field) {
            $user->save(); // Re-encrypts with new key
        }
    }
});
```

---

## Custom Key Storage Handlers

The package is designed to be flexible and extensible. The default `RsaKeyStorageHandler` stores keys in the local filesystem, but you can **implement your own custom key storage** to integrate with external systems like [HashiCorp Vault](https://www.vaultproject.io/), AWS KMS, Azure Key Vault, or databases.

### How to Implement a Custom Handler

1. **Create a class** that implements the `RsaKeyHandler` interface:

```php
<?php

namespace App\Encryption;

use RichardStyles\EloquentEncryption\Contracts\RsaKeyHandler;

class VaultKeyHandler implements RsaKeyHandler
{
    public function exists(): bool
    {
        // Check if keys exist in your vault
    }

    public function getPublicKey(): string
    {
        // Retrieve public key from vault
    }

    public function getPrivateKey(): string
    {
        // Retrieve private key from vault
    }

    public function getPreviousKeys(): array
    {
        // Return array of previous key pairs with metadata:
        // [
        //   ['publickey' => '...', 'privatekey' => '...', 'rotated_at' => '...'],
        //   ...
        // ]
    }

    public function rotateKeys(string $newPublic, string $newPrivate): void
    {
        // Move current keys to previous, save new keys
    }

    // Implement remaining interface methods...
}
```

2. **Update the config** to use your custom handler:

```php
// config/eloquent_encryption.php
return [
    'handler' => \App\Encryption\VaultKeyHandler::class,
    // ... other config
];
```

That's it! The package will automatically use your custom handler for all key operations.

### Important Notes

- Your handler **must implement** `RichardStyles\EloquentEncryption\Contracts\RsaKeyHandler`
- The `getPreviousKeys()` method should return structured key pairs:
  ```php
  [
    [
      'publickey' => 'public key content',
      'privatekey' => 'private key content',
      'rotated_at' => '2026-02-22T14:45:00+00:00', // ISO 8601 timestamp
    ],
    // ... more previous keys
  ]
  ```
- Previous public keys are maintained for audit trails but aren't used for decryption
- Only previous **private keys** are used when decrypting data encrypted with old keys

### Default Handler (RsaKeyStorageHandler)

The default file-based handler stores keys in your Laravel `storage/` directory and uses a `.eloquent_encryption_metadata.json` file to track key rotation history. This metadata file is an **implementation detail** of the file-based handler and won't be relevant to custom handlers.

**Performance Consideration:** The default handler reads key files from disk on each encryption/decryption operation. For high-throughput applications, consider implementing a caching layer or using a custom handler with in-memory caching.

### Query Builder

A significant caveat with storing encrypted data in the database, is that you are unable to use your database provider to query against the column. Should you need to do this, then please be aware of the extra overhead as all rows would need to be processed in a collection using [cursors](https://laravel.com/docs/12.x/eloquent#cursors) and [lazy collection methods](https://laravel.com/docs/12.x/collections#lazy-collection-methods).

## Upgrading

### Upgrading to 4.x from 3.x

**⚠️ This is a major version with breaking changes.**

#### Version Requirements

**Version 4.x requires:**
- Laravel 12.x or 13.x (drops support for Laravel 8-11)
- PHP 8.2+ (drops support for PHP 8.0 and 8.1)
- phpseclib v3 (upgraded from v2)

**Before upgrading:**
1. Ensure your application is running Laravel 12+ and PHP 8.2+
2. Review the [CHANGELOG](CHANGELOG.md) for detailed changes
3. Test thoroughly in a non-production environment

**To upgrade:**
```bash
composer require richardstyles/eloquentencryption:^4.0
```

**Not ready to upgrade?** Continue using version 3.x:
```bash
composer require richardstyles/eloquentencryption:^3.0
```

#### Data Compatibility ✅

Your **existing encrypted data will continue to work** without any migration. The encryption algorithm and key format remain compatible. The upgrade only affects:
- Minimum Laravel/PHP versions
- Underlying phpseclib library implementation

#### What's New in 4.x

**phpseclib v3:**
- Better security with modern cryptographic implementations
- Improved performance
- Active maintenance and security updates

**Laravel 12-13 Optimization:**
- Full compatibility with latest Laravel Encrypter contract
- Includes `getAllKeys()` and `getPreviousKeys()` methods for key rotation support

#### Testing Framework

The test suite has been migrated from PHPUnit to Pest for better developer experience. This doesn't affect package functionality but provides:
- More readable test syntax
- Faster test execution
- Better error messages

If you were extending or contributing to this package, please note the new test structure.

### Testing

This package uses [Pest](https://pestphp.com/) for testing.

``` bash
composer test
```

Run with coverage:
``` bash
composer test-coverage
```

Run specific test files:
``` bash
vendor/bin/pest tests/Unit/EloquentEncryptionTest.php
```

### Code Style

This package uses [Laravel Pint](https://laravel.com/docs/pint) for code style formatting.

Run Pint to fix code style:
``` bash
composer lint
```

Check code style without making changes:
``` bash
composer lint:test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Support

If you are having general issues with this package, feel free to contact me on [Twitter](https://twitter.com/StylesGoTweet).

If you believe you have found an issue, please report it using the [GitHub issue tracker](https://github.com/RichardStyles/EloquentEncryption/issues), or better yet, fork the repository and submit a pull request with a failing test.

If you're using this package, I'd love to hear your thoughts. Thanks!

### Security

If you discover any security related issues, please email richard@udeploy.dev instead of using the issue tracker.

## Credits

- [Richard Styles](https://github.com/richardstyles)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
