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
            foreach (EbayListing::where('type', $shop->slug)->get() as $listing) {
                if ($listing->product->qty != $listing->product->old_qty || $listing->product->price != $listing->product->old_price)  {
                    dispatch(new UpdateInventoryPricingJob($listing));
                }
            }
        }

        return 'The Job started successfully!';
    }
}
