<?php

declare(strict_types=1);

namespace RichardStyles\EloquentEncryption\Casts;

use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Support\Collection;

class EncryptedCollection extends Encrypted
{
    /**
     * Cast the given value and decrypt
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return Collection
     */
    public function get($model, $key, $value, $attributes)
    {
        /** @var string $decrypted */
        $decrypted = parent::get($model, $key, $value, $attributes);

        if ($decrypted === null) {
            return new Collection;
        }

        return new Collection(json_decode($decrypted));
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  array  $value
     * @param  array  $attributes
     * @return string
     */
    public function set($model, $key, $value, $attributes)
    {
        if ($value instanceof Collection) {
            $value = $value->toJson();
        } else {
            $value = json_encode($value);

            if ($value === false) {
                throw JsonEncodingException::forAttribute(
                    $this,
                    $key,
                    json_last_error_msg()
                );
            }
        }

        return parent::set($model, $key, $value, $attributes);
    }
}
