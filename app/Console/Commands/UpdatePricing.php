<?php

namespace App\Console\Commands;

use App\Jobs\ReviseProductJob;
use App\Models\Backlog;
use App\Models\EbayListing;
use Illuminate\Console\Command;

class UpdatePricing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:pricing {--shop=ebay4}';

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
        $shop = $this->option('shop');
        if ($shop == 'ebay4') {
            foreach (EbayListing::where('type', 'ebay4')->get() as $listing) {
                if ($listing->product->qty != $listing->product->old_qty || $listing->product->price != $listing->product->old_price)  {
                    dispatch(new ReviseProductJob($listing));
                }
            }

        }
        return 'The Job started successfully!';
    }
}
