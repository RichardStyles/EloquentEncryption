<?php

namespace RichardStyles\EloquentEncryption\Tests;

use RichardStyles\EloquentEncryption\EloquentEncryptionFacade;

class ModelCustomEncryptorTestCase extends TestCase
{
    protected function getPackageAliases($app)
    {
        return [
            'EloquentEncryption' => EloquentEncryptionFacade::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
