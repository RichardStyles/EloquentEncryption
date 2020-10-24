<?php

namespace RichardStyles\EloquentEncryption\Tests;

use Orchestra\Testbench\TestCase;
use RichardStyles\EloquentEncryption\EloquentEncryptionServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [EloquentEncryptionServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
