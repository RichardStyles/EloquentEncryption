<?php

declare(strict_types=1);

namespace RichardStyles\EloquentEncryption\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use RichardStyles\EloquentEncryption\EloquentEncryptionFacade;

/**
 * @implements CastsAttributes<mixed, mixed>
 */
class Encrypted implements CastsAttributes
{
    /**
     * Cast the given value and decrypt
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array<string, mixed>  $attributes
     */
    public function get(mixed $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) {
            return null;
        }

        return EloquentEncryptionFacade::decryptString($value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array<string, mixed>  $attributes
     */
    public function set(mixed $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) {
            return null;
        }

        return EloquentEncryptionFacade::encryptString($value);
    }
}
