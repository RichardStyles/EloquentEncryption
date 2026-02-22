<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use RichardStyles\EloquentEncryption\EloquentEncryption;
use RichardStyles\EloquentEncryption\EloquentEncryptionFacade;

beforeEach(function () {
    // Setup SQLite in-memory database
    Config::set('database.default', 'testbench');
    Config::set('database.connections.testbench', [
        'driver'   => 'sqlite',
        'database' => ':memory:',
        'prefix'   => '',
    ]);

    // Register facade alias
    if (!class_exists('EloquentEncryption')) {
        class_alias(EloquentEncryptionFacade::class, 'EloquentEncryption');
    }

    $this->eloquentEncryption = $this->mock(EloquentEncryption::class);

    Schema::create('encrypted_casts', function (Blueprint $table) {
        $table->increments('id');
        $table->string('secret', 1000)->nullable();
        $table->text('secret_array')->nullable();
        $table->text('secret_json')->nullable();
        $table->text('secret_object')->nullable();
        $table->text('secret_collection')->nullable();
    });

    expect(Model::$encrypter)->toBeNull();

    Model::encryptUsing($this->eloquentEncryption);
});

test('a model can encrypt using eloquent encryption', function () {
    $this->eloquentEncryption->expects('encrypt')
        ->with('this is a secret string', false)
        ->andReturn('encrypted-secret-string');
    $this->eloquentEncryption->expects('decrypt')
        ->with('encrypted-secret-string', false)
        ->andReturn('this is a secret string');

    /** @var \Illuminate\Tests\Integration\Database\EncryptedCast $subject */
    $subject = EncryptedCast::create([
        'secret' => 'this is a secret string',
    ]);

    expect($subject->secret)->toBe('this is a secret string');

    $this->assertDatabaseHas('encrypted_casts', [
        'id' => $subject->id,
        'secret' => 'encrypted-secret-string',
    ]);
});

/**
 * @property $secret
 * @property $secret_array
 * @property $secret_json
 * @property $secret_object
 * @property $secret_collection
 */
class EncryptedCast extends Model
{
    public $timestamps = false;
    protected $guarded = [];

    public $casts = [
        'secret' => 'encrypted',
        'secret_array' => 'encrypted:array',
        'secret_json' => 'encrypted:json',
        'secret_object' => 'encrypted:object',
        'secret_collection' => 'encrypted:collection',
    ];
}
