<?php

namespace App\Jobs;

use App\Helpers\EbayHelper;
use App\Models\Backlog;
use App\Models\EbayListing;
use App\Models\Shop;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class UpdateInventoryPricingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $listing;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(EbayListing $listing)
    {
        $this->listing = $listing;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $listingInfo = $this->listing->getPriceGraph(true);
        $listingPrice = DB::table('listing_price')->where('listing_id', $this->listing->id);
        $nums = $listingInfo[0]['nums'];
        if ($nums == 0) $nums = 1;
        if ($listingPrice->exists()) {
            $listingPrice->update(['price' => $listingInfo['price'], 'price_old' => $listingPrice->first()->price, 'quantity' => $listingInfo[0]['qty'] / $nums]);
            Backlog::createBacklog('updateInventory', 'Listing id:'.$this->listing->id.' Ebay id:'.$this->listing->ebay_id.' updated successful');
        }
        else {
            DB::table('listing_price')->insert(['listing_id' => $this->listing->id, 'price' => $listingInfo['price'], 'quantity' => $listingInfo[0]['qty'] / $nums]);
        }
        $ebayUploader = new EbayHelper(Shop::where('slug', $this->listing->shop)->first());
        $ebayUploader->updateInventoryPricing($this->listing);
    }
}
