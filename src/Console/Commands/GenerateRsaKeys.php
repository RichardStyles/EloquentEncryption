<?php


namespace RichardStyles\EloquentEncryption\Console\Commands;


use Illuminate\Console\Command;
use RichardStyles\EloquentEncryption\EloquentEncryptionFacade;

class GenerateRsaKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'encrypt:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This generates the application level RSA keys ' .
    'which are used to encrypt the application data at rest';

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
     * @return mixed
     */
    public function handle()
    {
        // check for existing keys - do not overwrite
        if(EloquentEncryptionFacade::exists()){

            $this->warn('Application RSA keys are already set');
            $this->warn("**********************************************************************");
            $this->warn("* If you reset your keys you will lose access to any encrypted data. *");
            $this->warn("**********************************************************************");
            if ($this->confirm('Do you wish to reset your encryption keys?') === false) {

                $this->info("RSA Keys have not been overwritted");

                return;
            }
        }

        $this->info("Creating RSA Keys for Application");
        EloquentEncryptionFacade::makeEncryptionKeys();

    }
}
