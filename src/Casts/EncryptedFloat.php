<?php

declare(strict_types=1);

namespace RichardStyles\EloquentEncryption\Casts;

class EncryptedFloat extends Encrypted
{
    /**
     * Cast the given value and decrypt
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array<string, mixed>  $attributes
     */
    public function get(mixed $model, string $key, mixed $value, array $attributes): float
    {
        return $this->fromFloat(parent::get($model, $key, $value, $attributes));
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

    /**
     * Decode the given float.
     *
     * @param  mixed  $value
     */
    public function fromFloat($value): float
    {
        switch ((string) $value) {
            case 'Infinity':
                return INF;
            case '-Infinity':
                return -INF;
            case 'NaN':
                return NAN;
            default:
                return (float) $value;
        }
    }
}
