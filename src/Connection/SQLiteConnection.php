<?php


namespace RichardStyles\EloquentEncryption\Connection;


use Illuminate\Database\SQLiteConnection as BaseSQLiteConnection;
use RichardStyles\EloquentEncryption\Schema\Grammars\SQLiteGrammar;

class SQLiteConnection extends BaseSQLiteConnection
{
    /**
     * Get the default schema grammar instance.
     *
     * @return \Illuminate\Database\Grammar
     */
    protected function getDefaultSchemaGrammar()
    {
        return $this->withTablePrefix(new SQLiteGrammar());
    }
}
