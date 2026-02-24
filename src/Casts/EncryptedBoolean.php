<?php

declare(strict_types=1);

namespace RichardStyles\EloquentEncryption\Casts;

class EncryptedBoolean extends Encrypted
{
    /**
     * Cast the given value and decrypt
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array<string, mixed>  $attributes
     */
    public function get(mixed $model, string $key, mixed $value, array $attributes): bool
    {
        return (bool) parent::get($model, $key, $value, $attributes);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array<string, mixed>  $attributes
     */
    public function set(mixed $model, string $key, mixed $value, array $attributes): mixed
    {
        return parent::set($model, $key, $value, $attributes);
    }
}
