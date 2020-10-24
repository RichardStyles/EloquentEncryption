<?php


namespace RichardStyles\EloquentEncryption\Connection;


use Illuminate\Database\PostgresConnection as BasePostgresConnection;
use RichardStyles\EloquentEncryption\Schema\Grammars\PostgresGrammar;

class PostgresConnection extends BasePostgresConnection
{
    /**
     * Get the default schema grammar instance.
     *
     * @return \Illuminate\Database\Grammar
     */
    protected function getDefaultSchemaGrammar()
    {
        return $this->withTablePrefix(new PostgresGrammar);
    }
}
