<?php


namespace RichardStyles\EloquentEncryption\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use RichardStyles\EloquentEncryption\EloquentEncryptionFacade;

class Encrypted implements CastsAttributes
{
    /**
     * Cast the given value and decrypt
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return array
     */
    public function get($model, $key, $value, $attributes)
    {
        return EloquentEncryptionFacade::decrypt($value);
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
        return EloquentEncryptionFacade::encrypt($value);
    }
}
