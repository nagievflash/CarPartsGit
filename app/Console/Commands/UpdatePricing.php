<?php

namespace App\Console\Commands;

use App\Jobs\UpdateInventoryPricingJob;
use App\Models\EbayListing;
use App\Models\Shop;
use Illuminate\Console\Command;

class UpdatePricing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:pricing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update inventory pricing on Ebay';

    /**
     * Execute the console command.
     *
     * @return string
     */
    public function handle(): string
    {
        foreach (Shop::all() as $shop) {
            foreach (EbayListing::all() as $listing) {
                dispatch(new UpdateInventoryPricingJob($listing));
            }
        }

        return 'The Job started successfully!';
    }
}
