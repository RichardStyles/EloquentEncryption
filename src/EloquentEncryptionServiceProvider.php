<?php

namespace RichardStyles\EloquentEncryption;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Support\Fluent;
use Illuminate\Support\ServiceProvider;
use RichardStyles\EloquentEncryption\Console\Commands\GenerateRsaKeys;
use RichardStyles\EloquentEncryption\Exceptions\UnknownGrammarClass;

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
        Grammar::macro('typeEncrypted', function (Fluent $column) {
            $className = (new \ReflectionClass($this))->getShortName();

            if ($className === "MySqlGrammar") {
                return 'blob';
            }

            if ($className === "PostgresGrammar") {
                return 'bytea';
            }

            if ($className === "SQLiteGrammar") {
                return 'blob';
            }

            if ($className === "SqlServerGrammar") {
                return 'varbinary(max)';
            }

            throw new UnknownGrammarClass;
        });


        Blueprint::macro('encrypted', function ($column): ColumnDefinition {
            /** @var Blueprint $this */
            return $this->addColumn('encrypted', $column);
        });

        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/eloquent_encryption.php', 'eloquent_encryption');

        // Register the main class to use with the facade
        $this->app->singleton('eloquentencryption', function () {
            return new EloquentEncryption;
        });
    }
}
