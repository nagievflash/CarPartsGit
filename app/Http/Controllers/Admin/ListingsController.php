<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EbayListing;
use App\Models\Product;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

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
}
