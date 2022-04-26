<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App;

class CryptoTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crypto:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run test code';

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
        return 0;
    }
}
