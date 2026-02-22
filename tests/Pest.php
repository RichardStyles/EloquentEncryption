<?php

use RichardStyles\EloquentEncryption\Tests\TestCase;
use RichardStyles\EloquentEncryption\Tests\ModelCustomEncryptorTestCase;
use RichardStyles\EloquentEncryption\Tests\Traits\WithRSAHelpers;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Uses the base TestCase which extends Orchestra\Testbench\TestCase
| and provides package-specific setup (service providers, etc.)
|
| IMPORTANT: Specific test case configurations must come before general ones
|
*/

// All Unit tests use base TestCase
uses(TestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Traits
|--------------------------------------------------------------------------
|
| Make traits available via uses() in individual test files
|
*/

uses(WithRSAHelpers::class)->in('Unit/EloquentEncryptionTest.php');
uses(WithRSAHelpers::class)->in('Unit/StorageHandlerTest.php');
uses(WithRSAHelpers::class)->in('Unit/ModelCustomEncryptorTest.php');
