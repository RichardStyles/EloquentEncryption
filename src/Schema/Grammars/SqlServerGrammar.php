<?php


namespace RichardStyles\EloquentEncryption\Schema\Grammars;

use Illuminate\Support\Fluent;
use Illuminate\Database\Schema\Grammars\SqlServerGrammar as IlluminateSqlServerGrammar;

class SqlServerGrammar extends IlluminateSqlServerGrammar
{
    /**
     * Create the column definition for a encrypted type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     *
     * @return string
     */
    protected function typeEncrypted()
    {
        return 'varbinary(max)';
    }
}
