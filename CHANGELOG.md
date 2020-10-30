# Changelog

All notable changes to `eloquentencryption` will be documented in this file

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
