# Eloquent Encryption

This package enables an additional layer of security when handling sensitive data. Allowing key fields of your eloquent models in the database to be encrypted at rest.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/richardstyles/eloquentencryption.svg?style=flat-square)](https://packagist.org/packages/richardstyles/eloquentencryption)
[![Build Status](https://img.shields.io/travis/richardstyles/eloquentencryption/master.svg?style=flat-square)](https://travis-ci.org/richardstyles/eloquentencryption)
[![Quality Score](https://img.shields.io/scrutinizer/g/richardstyles/eloquentencryption.svg?style=flat-square)](https://scrutinizer-ci.com/g/richardstyles/eloquentencryption)
[![Total Downloads](https://img.shields.io/packagist/dt/richardstyles/eloquentencryption.svg?style=flat-square)](https://packagist.org/packages/richardstyles/eloquentencryption)

## Introduction

This open source package fulfils the need of encrypting selected model data in your database whilst allowing your app:key to be rotated. When needing to store private details this package allows for greater security than the default Laravel encrypter. 
It uses default 4096-bit RSA keys to encrypt your data securely and Laravel model casting to dynamically encrypt and decrypt key fields. 

Usually, you would use [Laravel's Encrypter](https://laravel.com/docs/8.x/encryption) to encrypt the data, but this has the limitation of using the `app:key` as the private secret. As the app key also secures session/cookie data, it is [advised that you rotate this every so often](https://tighten.co/blog/app-key-and-you/) - if you're storing encrypted data using this method you have to decrypt it all first and re-encrypt whenever this is done. Therefore this package improves on this by creating a separate and stronger encryption process allowing you to rotate the app:key. This allows for a greater level of security of sensitive model data within your Laravel application and your database.

## Installation

This package requires Laravel 8.x or higher.

You can install the package via composer:

```bash
composer require richardstyles/eloquentencryption
```

Register the service provider in your `config/app.php` configuration file, this is needed for the migration helper.

```php
'providers' => [
    ...
    RichardStyles\EloquentEncryption\EloquentEncryptionServiceProvider::class,
],
```

The configuration can be published using this command, if you need to change the RSA key size, storage path and key file names.

```bash
php artisan vendor:publish --provider="RichardStyles\EloquentEncryption\EloquentEncryptionServiceProvider" --tag="config"
```

In order to encrypt and decrypt data you need to generate RSA keys for this package. By default, this will create 4096-bit RSA keys to your `storage/` directory.

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
    $table->timestamps();
});
```

## Usage

This package leverages Laravel's own [custom casting](https://laravel.com/docs/8.x/eloquent-mutators#custom-casts) to encode/decode values. 

``` php
<?php

namespace App\Models;

use RichardStyles\EloquentEncryption\Casts\Encrypted;
use Illuminate\Database\Eloquent\Model;

class SalesData extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'private_data' => Encrypted::class,
    ];
}

```

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
