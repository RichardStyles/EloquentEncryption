<?php


namespace RichardStyles\EloquentEncryption\Connection;


use Illuminate\Database\MySqlConnection as BaseMySqlConnection;
use RichardStyles\EloquentEncryption\Schema\Grammars\MySqlGrammar;

class MySqlConnection extends BaseMySqlConnection
{
    /**
     * Get the default schema grammar instance.
     *
     * @return \Illuminate\Database\Grammar
     */
    protected function getDefaultSchemaGrammar()
    {
        return $this->withTablePrefix(new MySqlGrammar);
    }
}
