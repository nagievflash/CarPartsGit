<?php

namespace App\Jobs;

use App\Helpers\EbayHelper;
use App\Models\Backlog;
use App\Models\EbayListing;
use App\Models\Product;
use App\Models\Shop;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redirect;

class AddFixedPriceItemJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $product;

    public string $shop;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Product $product, string $shop='ebay4')
    {
        $this->product = $product;
        $this->shop = $shop;
    }

    /**
     * Execute the job.
     *
     * @throws Exception
     */
    public function handle()
    {
        //Backlog::createBacklog('pricingUpdate', 'Ebay4 Listing updated sku ' . $this->listing->sku);
        $ebayUploader = new EbayHelper(Shop::where('slug', $this->shop)->first());
        $response = $ebayUploader->addFixedPriceItem($this->product);

        if ($response->body()) {
            $body = simplexml_load_string($response->body());
            if (isset($body->ItemID)) {
                EbayListing::create([
                    'sku'       => $this->product->sku,
                    'ebay_id'   => $body->ItemID,
                    'type'      => $this->shop
                ]);
            }
        }
    }
}
