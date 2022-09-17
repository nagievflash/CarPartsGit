<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\EbayListing;
use App\Models\Fitment;
use App\Models\Shop;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Ebay products import page
     * @param Request $request
     * @return Factory|View|Application
     */
    public function importProductsFromCSV(Request $request): Factory|View|Application
    {
        $code = $request->has('code') ? $request->input('code') : '';
        return view('admin.importCSVItem')->with('code', $code);
    }

    /**
     * Ebay listings page
     * @param Request $request
     * @return Factory|View|Application
     */
    public function ebayListings(Request $request): Factory|View|Application
    {
        if ($request->has('search')) {
            $listings = EbayListing::where("sku", $request->get("search"))->exists() ? EbayListing::where("sku", $request->get("search"))->paginate(15) : EbayListing::where("ebay_id", $request->get("search"))->paginate(15);
        }
        else $listings = EbayListing::paginate(15);
        return view('ebay.listings')->with('listings', $listings);
    }

    /**
     * Ebay upload page
     * @param Request $request
     * @return Factory|View|Application
     */
    public function ebayUpload(Request $request): Factory|View|Application
    {
        return view('admin.ebayUpload');
    }

    /**
     * Settings page
     * @param Request $request
     * @return Factory|View|Application
     */
    public function settings(Request $request): Factory|View|Application
    {
        $shops = Shop::get();
        return view('admin.settings')->with('shops', $shops);
    }


    /**
     * Show Ebay Template by slug
     */
    public function showTemplate(string $slug): string
    {
        $product = EbayListing::first()->product()->first();

        $fitments = Fitment::where('sku', $product->sku)->get();
        $attributes = Attribute::where('sku', $product->sku)->get();

        $result = $fitments->groupBy(['submodel_name']);
        $fits = array();
        foreach ($result as $items) {
            foreach ($items as $item) {
                $fits[] = array('name' => $item->make_name . ' ' . $item->model_name . ' ' . $item->submodel_name, 'year' => $item->year );
            }
        }
        $fitmentItems = array();
        $collection = collect($fits);
        foreach ($collection->groupBy('name') as $item) {
            $fitmentItems[] = count($item) > 1 ? $item[0]['name'] . ' ' . $item[0]['year'] . '-' . $item[count($item) - 1]['year'] : $item[0]['name'] . ' ' . $item[0]['year'];
        }
        rsort($fitmentItems);

        return View('ebay.templates.'.$slug, [
            'title'         => $product->title,
            'fitments'      => $fitmentItems,
            'attributes'    => $attributes,
            'images'        => explode(',', $product->images)
        ])->render();
    }


    /**
     * Ebay upload fitments page
     * @param Request $request
     * @return Factory|View|Application
     */
    public function importFitments(Request $request): Factory|View|Application
    {
        return view('admin.importFitments');
    }
}
