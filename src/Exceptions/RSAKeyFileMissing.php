<?php

declare(strict_types=1);

namespace RichardStyles\EloquentEncryption\Exceptions;

class RSAKeyFileMissing extends \Exception
{
    /**
     * @var string
     */
    protected $message = 'Eloquent Encryption RSA keys cannot be found.';
}
