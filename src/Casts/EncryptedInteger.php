<?php


namespace RichardStyles\EloquentEncryption\Casts;


class EncryptedInteger extends Encrypted
{
    /**
     * Cast the given value and decrypt
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return int
     */
    public function get($model, $key, $value, $attributes)
    {
        return intval(parent::get($model, $key, $value, $attributes));
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
        return parent::set($model, $key, $value, $attributes);
    }
}
