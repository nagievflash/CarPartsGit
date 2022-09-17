<?php

namespace App\Console\Commands;

use App\Jobs\ReviseProductJob;
use App\Models\Backlog;
use App\Models\EbayListing;
use App\Models\Shop;
use Illuminate\Console\Command;

class ReviseAllItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:listings {--limit=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all listings on Ebay';

    /**
     * Execute the console command.
     *
     * @return string
     */
    public function handle(): string
    {
        foreach (EbayListing::all() as $listing) {
            // dispatch(new ReviseProductJob($listing));
            dispatch(new ReviseProductJob($listing));
        }

        return 'The Job started successfully!';
    }
}
