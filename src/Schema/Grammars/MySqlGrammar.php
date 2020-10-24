<?php


namespace RichardStyles\EloquentEncryption\Schema\Grammars;

use Illuminate\Support\Fluent;
use Illuminate\Database\Schema\Grammars\MySqlGrammar as BaseMySqlGrammar;

class MySqlGrammar extends BaseMySqlGrammar
{
    /**
     * Create the column definition for a encrypted type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     *
     * @return string
     */
    protected function typeEncrypted(Fluent $column)
    {
        return 'blob';
    }
}
