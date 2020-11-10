<?php


namespace RichardStyles\EloquentEncryption\Tests\Unit;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Schema;
use RichardStyles\EloquentEncryption\EloquentEncryption;
use RichardStyles\EloquentEncryption\EloquentEncryptionFacade;
use RichardStyles\EloquentEncryption\Tests\TestCase;
use RichardStyles\EloquentEncryption\Tests\Traits\WithRSAHelpers;

class ModelCustomEncryptorTest extends TestCase
{
    use WithRSAHelpers;

    protected $eloquent_encryption;

    protected function getPackageAliases($app)
    {
        return [
            'EloquentEncryption' => EloquentEncryptionFacade::class
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->eloquentEncryption = $this->mock(EloquentEncryption::class);

        Schema::create('encrypted_casts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('secret', 1000)->nullable();
            $table->text('secret_array')->nullable();
            $table->text('secret_json')->nullable();
            $table->text('secret_object')->nullable();
            $table->text('secret_collection')->nullable();
        });

        $this->assertNull(Model::$encrypter);

        Model::encryptUsing($this->eloquentEncryption);
    }

    /** @test */
    function a_model_can_encrypt_using_eloquent_encryption()
    {

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

        $this->assertSame('this is a secret string', $subject->secret);
        $this->assertDatabaseHas('encrypted_casts', [
            'id' => $subject->id,
            'secret' => 'encrypted-secret-string',
        ]);
    }
}

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
