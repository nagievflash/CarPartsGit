<?php

namespace App\Jobs;

use App\Helpers\EbayUploadHelper;
use App\Models\EbayListing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReviseProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $listing;
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
        $ebayUploader = new EbayUploadHelper();
        $ebayUploader->reviseFixedPriceItem($this->listing);
    }
}
