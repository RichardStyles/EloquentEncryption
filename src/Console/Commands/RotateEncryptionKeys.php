<?php

namespace RichardStyles\EloquentEncryption\Console\Commands;

use Illuminate\Console\Command;
use RichardStyles\EloquentEncryption\EloquentEncryptionFacade;

class RotateEncryptionKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'encrypt:rotate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rotate RSA encryption keys while preserving access to data encrypted with previous keys';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Check if current keys exist
        if (! EloquentEncryptionFacade::exists()) {
            $this->error('No encryption keys found. Please run encrypt:generate first.');

            return 1;
        }

        // Warn about rotation
        $this->warn('This will generate new RSA keys and move the current keys to the previous keys list.');
        $this->info('Data encrypted with old keys will still be decryptable.');

        // Confirm action
        if (! $this->confirm('Do you wish to rotate your encryption keys?', true)) {
            $this->info('Key rotation cancelled.');

            return 0;
        }

        // Perform rotation
        $this->info('Rotating RSA keys...');
        EloquentEncryptionFacade::rotateKeys();

        $this->info('✓ RSA keys rotated successfully!');
        $this->info('New keys are now active. Previous keys are maintained for decryption.');

        return 0;
    }
}
