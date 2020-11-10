# Changelog

All notable changes to `eloquentencryption` will be documented in this file

## 3.0

- As of **Laravel 8.14** you can specify the built in Eloquent Encryption casting setting a model's encryptUsing in your app service provider. This allows for automatic separation of your APP_KEY, when using a different `Illuminate\Contracts\Encryption\Encrypter` class/instance.
 
```php
EncryptedCast::encryptUsing(new \RichardStyles\EloquentEncryption\EloquentEncryption);
```

Then simply define your casts in your model as you normally would.

```php
class EncryptedCast extends Model
{
    public $casts = [
        'secret' => 'encrypted',
        'secret_array' => 'encrypted:array',
        'secret_json' => 'encrypted:json',
        'secret_object' => 'encrypted:object',
        'secret_collection' => 'encrypted:collection',
    ];
}
```

## 2.0
- EloquentEncryption now uses `Illuminate\Contracts\Encryption\Encrypter` contract.
- **BC** If relying on 1.x use `encryptString()` or `decryptString()` functions **if you are using this encryption elsewhere in your application**. As the default encrypt/decrypt function now serialize values automatically, this may cause unexpected errors during decrypting.

## 1.5
- Add `EncryptedBoolean` cast by @valorin [#3](https://github.com/RichardStyles/EloquentEncryption/pull/3)

## 1.4
- Add optional support to define `RsaKeyHandler` to store, retrieved generated RSA keys.

## 1.3
- bug fix

## 1.2.0 
- Add additional Cast classes.
- `EncryptedInteger` 
- `EncryptedFloat`
- `EncryptedCollection`

## 1.1.1 - 2020-10-27
- Update README.md

## 1.1.0 - 2020-10-26
- Refactor how blueprint helper's are included.

## 1.0.0 - 2020-10-25

- initial release
- Adds `encrypted` field type to migrations blueprints.
- Adds `encrypt:generate` command to create RSA keys.
- Adds `Encrypted` cast to encode/decode the fields which have been set on a model.
