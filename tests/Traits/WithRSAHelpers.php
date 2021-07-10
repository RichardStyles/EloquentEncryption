<?php


namespace RichardStyles\EloquentEncryption\Tests\Traits;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use phpseclib\Crypt\RSA;

trait WithRSAHelpers
{
    use WithFaker;

    public function validateRSAKeys($public, $private)
    {
        $rsa = new RSA();
        // create signature using private key
        $rsa->loadKey($private);
        $plaintext = $this->faker->paragraph;
        $signature = $rsa->sign($plaintext);

        // load the public key to validate signature
        $rsa->loadKey($public);

        $this->assertTrue($rsa->verify($plaintext, $signature));
    }

    public function keysExistsInStorage($public, $private)
    {
        Storage::assertExists($public);
        Storage::assertExists($private);
    }

    public function makePrivateKey($path = '', $disk = null)
    {
        return $this->makeKey(
            $path,
            'eloquent_encryption',
            $disk
        );
    }

    public function makePublicKey($path = '', $disk = null)
    {
        return $this->makeKey(
            $path,
            'eloquent_encryption.pub',
            $disk
        );
    }

    private function makeKey($path, $key, $disk = null)
    {
        // create fake files to act as both the rsa keys
        $file = UploadedFile::fake()->create($key, 250);

        Storage::disk($disk)->put(
            trim($path, '/').'/'.$key,
            $file
        );

        return $key;
    }
}
