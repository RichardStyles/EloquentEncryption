<?php

namespace RichardStyles\EloquentEncryption\Schema\Grammars;

use Illuminate\Support\Fluent;
use Illuminate\Database\Schema\Grammars\PostgresGrammar as BasePostgresGrammar;

class PostgresGrammar extends BasePostgresGrammar
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
        return 'bytea';
    }
}
