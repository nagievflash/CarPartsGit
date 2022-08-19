<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EbayListing;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function importProductsFromCSV(Request $request) {
        $code = $request->has('code') ? $request->input('code') : '';
        return view('admin.importCSVItem')->with('code', $code);
    }

    public function ebayListings(Request $request) {
        $listings = EbayListing::paginate(25);
        return view('ebay.listings')->with('listings', $listings);
    }

    public function ebayUpload(Request $request) {
        return view('admin.ebayUpload');
    }
}
