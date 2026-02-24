<?php

namespace RichardStyles\EloquentEncryption\Tests\Unit;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\PostgresGrammar;
use Mockery;
use RichardStyles\EloquentEncryption\Tests\TestCase;

class DatabasePostgresSchemaGrammarTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_adding_encrypted_column()
    {
        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('getTablePrefix')->andReturn('');
        $grammar = new PostgresGrammar($connection);
        $connection->shouldReceive('getSchemaGrammar')->andReturn($grammar);

        $blueprint = new Blueprint($connection, 'users', function ($table) {
            $table->encrypted('foo');
        });

        $this->assertEquals(
            ['alter table "users" add column "foo" bytea not null'],
            $blueprint->toSql($connection, $grammar)
        );
    }
}
