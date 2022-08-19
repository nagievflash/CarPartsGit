<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\EbayUploadHelper;
use App\Http\Controllers\Controller;
use App\Models\EbayListing;
use App\Models\Product;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class EbayController extends Controller
{


    /**
     * This method is adding fixed price items to Ebay Listings
     * Using Ebay Trading API
     * @throws Exception
     */
    public function addFixedPriceItem(Request $request): RedirectResponse
    {
        $sku = $request->get('sku') ? $request->get('sku') : $request->input('sku');
        $product = Product::where('sku', $sku)->first();
        $ebayUploader = new EbayUploadHelper();
        $response = $ebayUploader->addFixedPriceItem($product);

        if ($response->body()) {
            $body = simplexml_load_string($response->body());
            if (isset($body->ItemID)) {
                EbayListing::create([
                    'sku'       => $product->sku,
                    'ebay_id'   => $body->ItemID
                ]);
                return Redirect::back()->with('success', 'The listing successful uploaded on Ebay. <a href="https://www.ebay.com/itm/'.$body->ItemID.'" target="_blank">See the listing</a>');
            }
            else return Redirect::back()->with('error', 'Error, this item already uploaded on ebay or server responded with an error');
        }
        else return Redirect::back()->with('error', 'Error, this item already uploaded on ebay or server responded with an error');
    }


    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws Exception
     */
    public function reviseFixedPriceItem(Request $request): RedirectResponse
    {
        $listing = EbayListing::where('ebay_id', $request->input('ebay_id'))->firstOrFail();

        $ebayUploader = new EbayUploadHelper();

        $response = $ebayUploader->reviseFixedPriceItem($listing);

        if ($response->body()) {
            return Redirect::back()->with('success', 'The listing successful revised on Ebay.');
        }
        else return Redirect::back()->with('error', 'Error, while sending request to Ebay API');

        /*
        $response = $ebayUploader->addFixedPriceItem($listing);
        return response($response->body(), 200, [
            'Content-Type' => 'application/xml'
        ]);
        */
    }
}
