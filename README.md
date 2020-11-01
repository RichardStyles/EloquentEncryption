# Eloquent Encryption

This package enables an additional layer of security when handling sensitive data. Allowing key fields of your eloquent models in the database to be encrypted at rest.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/richardstyles/eloquentencryption.svg?style=flat-square)](https://packagist.org/packages/richardstyles/eloquentencryption)
[![Build Status](https://img.shields.io/travis/richardstyles/eloquentencryption/master.svg?style=flat-square)](https://travis-ci.org/richardstyles/eloquentencryption)
[![Quality Score](https://img.shields.io/scrutinizer/g/richardstyles/eloquentencryption.svg?style=flat-square)](https://scrutinizer-ci.com/g/richardstyles/eloquentencryption)
[![Total Downloads](https://img.shields.io/packagist/dt/richardstyles/eloquentencryption.svg?style=flat-square)](https://packagist.org/packages/richardstyles/eloquentencryption)

## Introduction

This open source package fulfils the need of encrypting selected model data in your database whilst allowing your app:key to be rotated. When needing to store private details this package allows for greater security than the default Laravel encrypter. 
It uses default 4096-bit RSA keys to encrypt your data securely and Laravel model casting to dynamically encrypt and decrypt key fields. 

Usually, you would use [Laravel's Encrypter](https://laravel.com/docs/8.x/encryption) to encrypt the data, but this has the limitation of using the `app:key` as the private secret. As the app key also secures session/cookie data, it is [advised that you rotate this every so often](https://tighten.co/blog/app-key-and-you/) - if you're storing encrypted data using this method you have to decrypt it all first and re-encrypt whenever this is done. Therefore this package improves on this by creating a separate and stronger encryption process allowing you to rotate the app:key. This allows for a  level of security of sensitive model data within your Laravel application and your database.

If you don't want to use RSA keys, then I have another package [Eloquent AES](https://github.com/RichardStyles/eloquent-aes) which uses a separate key `eloquent_key` to encrypt using AES-256-CBC.

## Installation

This package requires Laravel 8.x or higher.

You can install the package via composer:

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

There is also a helper function to define your encrypted fields in your migrations.
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

This package leverages Laravel's own [custom casting](https://laravel.com/docs/8.x/eloquent-mutators#custom-casts) to encode/decode values. 

``` php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RichardStyles\EloquentEncryption\Casts\Encrypted;
use RichardStyles\EloquentEncryption\Casts\EncryptedInteger;
use RichardStyles\EloquentEncryption\Casts\EncryptedFloat;
use RichardStyles\EloquentEncryption\Casts\EncryptedCollection;

class SalesData extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'private_data' => Encrypted::class,
        'private_int' => EncryptedInteger::class,
        'private_float' => EncryptedFloat::class,
        'private_collection' => EncryptedCollection::class,
    ];
}

```

There are additional casts which will cast the decrypted value into a specific data type. If there is not one that you need, simply make a PR including sufficient testing.

### Custom RSA Key Storage

If you want to store your RSA key another way, such as using [Hashicorp Vault](https://www.vaultproject.io/). From 1.4 you can change the config option `handler` to a specific class which uses the `RsaKeyHandler` contract.
By default, this package uses a storage handler, which saves the generated key pair to `storage/` and retrieved the contents of the keys when encryption or decryption are processed. This is something that should be considered as it could add latency to your application.

```php
    /**
     * This class can be overridden to define how the RSA keys are stored, checked for
     * existence and returned for Encryption and Decryption. This allows for keys to
     * be held in secure Vaults or through another provider.
     */
    'handler' => \RichardStyles\EloquentEncryption\FileSystem\RsaKeyStorageHandler::class,
```

### Query Builder

A significant caveat with storing encrypted data in the database, is that you are unable to use your database provider to query against the column. Should you need to do this, then please be aware of the extra overhead as all rows would need to be processed in a collection using [cursors](https://laravel.com/docs/8.x/eloquent#using-cursors) and [lazy collection methods](https://laravel.com/docs/8.x/collections#lazy-collection-methods).

### Testing

``` bash
composer test
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
