<?php

namespace App\Jobs;

use App\Helpers\EbayUploadHelper;
use App\Models\Backlog;
use App\Models\EbayListing;
use App\Models\Shop;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReviseProductJob implements ShouldQueue
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
        //Backlog::createBacklog('pricingUpdate', 'Ebay4 Listing updated sku ' . $this->listing->sku);
        $ebayUploader = new EbayUploadHelper(Shop::where('slug', $this->listing->type)->first());
        $ebayUploader->reviseFixedPriceItem($this->listing);
    }
}
