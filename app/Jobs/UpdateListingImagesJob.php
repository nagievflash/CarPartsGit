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

class UpdateListingImagesJob implements ShouldQueue
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
     * @throws \Exception
     */
    public function handle()
    {
        $ebayUploader = new EbayHelper(Shop::where('slug', $this->listing->shop)->first());
        $ebayUploader->reviseFixedPriceItemImages($this->listing);
    }
}
