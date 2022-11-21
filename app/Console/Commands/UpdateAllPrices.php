<?php

namespace App\Console\Commands;

use App\Jobs\UpdateListingsPricesJob;
use App\Models\EbayListing;
use Illuminate\Console\Command;

class UpdateAllPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all prices in CRM';

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        foreach (EbayListing::all() as $listing) {
            dispatch(new UpdateListingsPricesJob($listing))->onQueue($listing->shop);
        }
    }
}
