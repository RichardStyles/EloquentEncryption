<?php


namespace RichardStyles\EloquentEncryption\Casts;


class EncryptedFloat extends Encrypted
{
    /**
     * Cast the given value and decrypt
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return int
     */
    public function get($model, $key, $value, $attributes)
    {
        return $this->fromFloat(parent::get($model, $key, $value, $attributes));
    }

    /**
     * Prepare the given value for storage.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param float $value
     * @param array $attributes
     * @return string
     */
    public function set($model, $key, $value, $attributes)
    {
        return parent::set($model, $key, $value, $attributes);
    }

    /**
     * Decode the given float.
     *
     * @param mixed $value
     * @return mixed
     */
    public function fromFloat($value)
    {
        switch ((string)$value) {
            case 'Infinity':
                return INF;
            case '-Infinity':
                return -INF;
            case 'NaN':
                return NAN;
            default:
                return (float)$value;
        }
    }
}
