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
        if ($this->listing->fixed != 0) $this->listing->updatePrice();
        $ebayUploader = new EbayHelper(Shop::where('slug', $this->listing->shop)->first());
        $ebayUploader->updateInventoryPricing($this->listing);
    }
}
