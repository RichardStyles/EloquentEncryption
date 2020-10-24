<?php

namespace RichardStyles\EloquentEncryption\Tests;

use RichardStyles\EloquentEncryption\EloquentEncryptionServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            EloquentEncryptionServiceProvider::class,
        ];
    }
}
