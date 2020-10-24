<?php

namespace RichardStyles\EloquentEncryption;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\ServiceProvider;
use RichardStyles\EloquentEncryption\Connection\MySqlConnection;
use RichardStyles\EloquentEncryption\Connection\PostgresConnection;
use RichardStyles\EloquentEncryption\Connection\SQLiteConnection;
use RichardStyles\EloquentEncryption\Console\Commands\GenerateRsaKeys;
use RichardStyles\EloquentEncryption\Schema\Grammars\SqlServerGrammar;

class EloquentEncryptionServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/eloquent_encryption.php' => config_path('eloquent_encryption.php'),
            ], 'config');

            // Registering package commands.
            $this->commands([
                GenerateRsaKeys::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config): MySqlConnection {
            return new MySqlConnection($connection, $database, $prefix, $config);
        });

        Connection::resolverFor('postgres', function ($connection, $database, $prefix, $config): PostgresConnection {
            return new PostgresConnection($connection, $database, $prefix, $config);
        });

        Connection::resolverFor('sqlite', function ($connection, $database, $prefix, $config): SQLiteConnection {
            return new SQLiteConnection($connection, $database, $prefix, $config);
        });

        Connection::resolverFor('sqlsrv', function ($connection, $database, $prefix, $config): SqlServerGrammar {
            return new SqlServerGrammar($connection, $database, $prefix, $config);
        });

        Blueprint::macro('encrypted', function ($column): ColumnDefinition {
            /** @var Blueprint $this */
            return $this->addColumn('encrypted', $column);
        });

        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/eloquent_encryption.php', 'eloquentencryption');

        // Register the main class to use with the facade
        $this->app->singleton('eloquentencryption', function () {
            return new EloquentEncryption;
        });
    }
}
