<?php

namespace RichardStyles\EloquentEncryption;

use Illuminate\Support\Facades\Facade;

/**
 * @see \RichardStyles\EloquentEncryption\Skeleton\SkeletonClass
 */
class EloquentEncryptionFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'eloquentencryption';
    }
}
