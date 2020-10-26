<?php

namespace RichardStyles\EloquentEncryption\Tests\Unit;

use Illuminate\Database\Schema\Grammars\SQLiteGrammar;
use Mockery;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use RichardStyles\EloquentEncryption\Tests\TestCase;

class DatabaseSQLiteSchemaGrammarTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testAddingEncryptedColumn()
    {
        $blueprint = new Blueprint('users', function ($table) {
            $table->encrypted('foo');
        });

        $connection = Mockery::mock(Connection::class);

        $this->assertEquals(
            ['alter table "users" add column "foo" blob not null'],
            $blueprint->toSql($connection, new SQLiteGrammar)
        );
    }
}
