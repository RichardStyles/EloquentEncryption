# Very short description of the package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/richardstyles/eloquentencryption.svg?style=flat-square)](https://packagist.org/packages/richardstyles/eloquentencryption)
[![Build Status](https://img.shields.io/travis/richardstyles/eloquentencryption/master.svg?style=flat-square)](https://travis-ci.org/richardstyles/eloquentencryption)
[![Quality Score](https://img.shields.io/scrutinizer/g/richardstyles/eloquentencryption.svg?style=flat-square)](https://scrutinizer-ci.com/g/richardstyles/eloquentencryption)
[![Total Downloads](https://img.shields.io/packagist/dt/richardstyles/eloquentencryption.svg?style=flat-square)](https://packagist.org/packages/richardstyles/eloquentencryption)

## Introduction

I find myself needing to store private details into a database, however I want to ensure that if a backup of the database was accessed and copied. Key information would not be readable.

## Installation

You can install the package via composer:

```bash
composer require richardstyles/eloquentencryption
```

Register the service provider in your `config/app.php` configuration file:

```php
'providers' => [
    ...
    RichardStyles\EloquentEncryption\EloquentEncryptionServiceProvider::class,
],
```

There is nothing special needed for this to function, simply declare a `encrypted` column type in your migration files. This simply creates a `binary`/`blob` column to hold the encrypted data.

```php
Schema::create('sales_notes', function (Blueprint $table) {
    $table->increments('id');
    $table->encrypted('private_data');
    $table->timestamps();
});
```

## Usage

In order to encrypt and decrypt data you need to generate RSA keys for this package. By default this will create 4096-bit RSA keys to your `storage/` directory.

```bash
php artisan encrypt:generate
```

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

### Security

If you discover any security related issues, please email richardstyles@gmail.com instead of using the issue tracker.

## Credits

- [Richard Styles](https://github.com/richardstyles)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
