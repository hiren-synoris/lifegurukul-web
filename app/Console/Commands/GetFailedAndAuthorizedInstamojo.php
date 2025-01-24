<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GetFailedAndAuthorizedInstamojo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-failed-and-authorized-instamojo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return Command::SUCCESS;
    }
}
