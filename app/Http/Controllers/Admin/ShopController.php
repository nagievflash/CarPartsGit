<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index(): Application|Factory|View
    {
        $shops = Shop::all();
        return view('admin.shop.index')->with('shops', $shops);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create(): Application|Factory|View
    {
        return view('admin.shop.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Redirector|Application|RedirectResponse
     */
    public function store(Request $request): Redirector|Application|RedirectResponse
    {
        $shop = Shop::firstOrCreate(
            [
                'slug' => $request->input('slug')
            ],
            [
                'title' => $request->input('title'),
                'slug' => $request->input('slug'),
                'email' => $request->input('email'),
                'store_url' => $request->input('store_url'),
                'username' => $request->input('username'),
                'token' => $request->input('token'),
                'percent' => $request->input('percent'),
                'max_qty' => $request->input('max_qty'),
                'qty_reserve' => $request->input('qty_reserve'),
                'shipping_profile_name' => $request->input('shipping_profile_name'),
                'shipping_profile_id' => $request->input('shipping_profile_id'),
                'return_profile_name' => $request->input('return_profile_name'),
                'return_profile_id' => $request->input('return_profile_id'),
                'payment_profile_name' => $request->input('payment_profile_name'),
                'payment_profile_id' => $request->input('payment_profile_id'),
            ]
        );

        return redirect('/admin/settings/shop/' . $shop->slug);
    }

    /**
     * Display the specified resource.
     *
     * @param  string $slug
     * @return Application|Factory|View
     */
    public function show(string $slug): Application|Factory|View
    {
        $shop = Shop::where('slug', $slug)->firstOrFail();
        return view('admin.shop.item')->with('shop', $shop);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param string $slug
     * @return Redirector|Application|RedirectResponse
     */
    public function update(Request $request, string $slug): Redirector|Application|RedirectResponse
    {
        $shop = Shop::where('slug', $slug)
            ->update([
            'title' => $request->input('title'),
            'slug' => $request->input('slug'),
            'email' => $request->input('email'),
            'store_url' => $request->input('store_url'),
            'username' => $request->input('username'),
            'token' => $request->input('token'),
            'percent' => $request->input('percent'),
            'max_qty' => $request->input('max_qty'),
            'qty_reserve' => $request->input('qty_reserve'),
            'shipping_profile_name' => $request->input('shipping_profile_name'),
            'shipping_profile_id' => $request->input('shipping_profile_id'),
            'return_profile_name' => $request->input('return_profile_name'),
            'return_profile_id' => $request->input('return_profile_id'),
            'payment_profile_name' => $request->input('payment_profile_name'),
            'payment_profile_id' => $request->input('payment_profile_id'),
        ]);

        return redirect('/admin/settings/shop/' . $slug);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string $slug
     * @return Response
     */
    public function destroy(string $slug)
    {
        $shop = Shop::where('slug', $slug)->firstOrFail();
        $shop->delete();
        return redirect('/admin/settings/shop/');
    }
}
