<?php


namespace RichardStyles\EloquentEncryption\Exceptions;


class InvalidRsaKeyHandler extends \Exception
{
    /**
     * @var string
     */
    protected $message = "Invalid Handler class. The Rsa Key Handler must implement RsaKeyHandler";
}
