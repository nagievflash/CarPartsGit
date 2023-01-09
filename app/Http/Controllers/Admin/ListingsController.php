<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EbayListing;
use App\Models\ListingPartslink;
use App\Models\ListingPrice;
use App\Models\Product;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class ListingsController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @param string $ebay_id
     * @return Factory|View|Application
     */
    public function show(string $ebay_id): Factory|View|Application
    {
        $listing = EbayListing::where('ebay_id', $ebay_id)->firstOrFail();
        return view('admin.listings.listing')->with('listing', $listing);
    }

    public function update(Request $request) {
        $id = $request->id;
        $fixed = $request->fixed == 'true' ? 1 : 0;
        $listingPrice = DB::table('listing_price')->where('listing_id', $id);
        $price = $request->has('price') ? $request->price : $listingPrice->first()->price;
        $qty = $request->has('qty') ? $request->qty : $listingPrice->first()->quantity;

        EbayListing::where('id', $id)->update(['fixed' => $fixed]);
        if ($listingPrice->exists()) {
            $listingPrice->update(['price' => $price, 'price_old' => $listingPrice->first()->price, 'quantity' => $qty]);
        }

        return $qty;
    }

    /**
     * Add Partslink
     * @param Request $request
     * @return string
     */
    public function storePartslink(Request $request) {
        $id         = $request->listing_id;
        $partslink  = $request->partslink;
        $quantity   = $request->quantity;
        ListingPartslink::updateOrCreate(
            ['listing_id' => $id, 'partslink' => $partslink],
            ['quantity' => $quantity]
        );

        return 'success';
    }

    public function removePartslink($id): string
    {
        ListingPartslink::where('id', $id)->delete();
        return 'Success deleted';
    }
}
