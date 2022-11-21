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
    protected $signature = 'update:pricing {shop=all}';

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
        if ($this->argument('shop') == 'all') {
            foreach (EbayListing::all() as $listing) {
                dispatch(new UpdateInventoryPricingJob($listing))->onQueue($listing->shop);
            }
        }
        else {
            foreach (EbayListing::where('shop', $this->argument('shop'))->get() as $listing) {
                dispatch(new UpdateInventoryPricingJob($listing))->onQueue($listing->shop);
            }
        }


        return 'The Job started successfully!';
    }
}
