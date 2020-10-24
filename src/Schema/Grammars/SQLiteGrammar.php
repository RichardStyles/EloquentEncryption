<?php


namespace RichardStyles\EloquentEncryption\Schema\Grammars;

use Illuminate\Support\Fluent;
use Illuminate\Database\Schema\Grammars\SQLiteGrammar as IlluminateSQLiteGrammar;

class SQLiteGrammar extends IlluminateSQLiteGrammar
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
        return 'blob';
    }
}
