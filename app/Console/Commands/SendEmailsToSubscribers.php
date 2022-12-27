<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SendEmailsToSubscribersJob;

class SendEmailsToSubscribers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:subs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a notification of receipt';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        dispatch(new SendEmailsToSubscribersJob());

        return 'The Job started successfully!';
    }
}
