<?php

use App\Mail\OrderConfirmation;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Vtiful\Kernel\Excel;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    return view('test');
});

Route::middleware(['auth:sanctum', 'verified'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::get('/import',  [App\Http\Controllers\Admin\AdminController::class, 'importProductsFromCSV'])->name('import');
    Route::get('/import/products/custom',  [App\Http\Controllers\Admin\AdminController::class, 'ebayUpload'])->name('ebayUpload');
    Route::get('/import/fitments/',  [App\Http\Controllers\Admin\AdminController::class, 'importFitments'])->name('importFitments');
    Route::get('/products',  [App\Http\Controllers\Admin\ProductsController::class, 'index'])->name('products.list');
    Route::get('/ebay/listings',  [App\Http\Controllers\Admin\AdminController::class, 'ebayListings'])->name('ebay.listings');
    Route::get('/ebay/listings/update',  [App\Http\Controllers\Admin\AdminController::class, 'updateListingId'])->name('updateListingId');
    Route::get('/import/categories',  [App\Http\Controllers\Admin\AdminController::class, 'categoriesImport'])->name('categoriesImport');
    Route::get('/import/lkq_packages',  [App\Http\Controllers\Admin\AdminController::class, 'importLKQPackages'])->name('lkq_packages');

    // Settings shop
    Route::get('/settings/',  [App\Http\Controllers\Admin\AdminController::class, 'settings'])->name('settings');
    Route::get('/settings/shop',  [App\Http\Controllers\Admin\ShopController::class, 'index'])->name('settings.shop');
    Route::get('/settings/suppliers',  [App\Http\Controllers\Admin\SuppliersController::class, 'index'])->name('settings.suppliers');
    Route::get('/settings/shop/create',  [App\Http\Controllers\Admin\ShopController::class, 'create'])->name('settings.shop.create');
    Route::get('/settings/shop/{slug}',  [App\Http\Controllers\Admin\ShopController::class, 'show']);
    Route::post('/settings/shop/store',  [App\Http\Controllers\Admin\ShopController::class, 'store'])->name('settings.shop.store');
    Route::put('/settings/shop/{slug}/update',  [App\Http\Controllers\Admin\ShopController::class, 'update']);
    Route::post('/settings/suppliers/lkq/update',  [App\Http\Controllers\Admin\SuppliersController::class, 'updateLKQ']);
    Route::delete('/settings/shop/{slug}/delete',  [App\Http\Controllers\Admin\ShopController::class, 'destroy']);
    Route::get('/settings/taxes',  [App\Http\Controllers\Admin\TaxesController::class, 'index'])->name('settings.taxes');


    // Route::post('/import',  [App\Http\Controllers\Admin\RequestController::class, 'importProductsBasics']);
    Route::post('/import/jc',  [App\Http\Controllers\Admin\RequestController::class, 'importInventoryJC']);
    Route::post('/import/updateEbayListingId',  [App\Http\Controllers\Admin\RequestController::class, 'updateEbayListingId']);
    Route::post('/import/fitments',  [App\Http\Controllers\Admin\RequestController::class, 'importFitments']);
    Route::post('/import/products/custom',  [App\Http\Controllers\Admin\RequestController::class, 'importProductsCustom']);
    Route::post('/import/lkq_packages',  [App\Http\Controllers\Admin\RequestController::class, 'importLKQPackages']);

    Route::post('/ebay/upload',  [App\Http\Controllers\Admin\EbayController::class, 'addFixedPriceItem']);
    Route::get('/ebay/revise',  [App\Http\Controllers\Admin\EbayController::class, 'reviseFixedPriceItem']);
    Route::get('/ebay/update_price',  [App\Http\Controllers\Admin\EbayController::class, 'updatePriceItem']);
    Route::post('/ebay/remove-listing/{id}',  [App\Http\Controllers\Admin\EbayController::class, 'removeListing']);
    Route::post('/ebay/update-listing/{id}',  [App\Http\Controllers\Admin\EbayController::class, 'updateListing']);
    Route::post('/categories/import',  [App\Http\Controllers\Admin\CategoriesController::class, 'categoriesImport']);
    Route::post('/taxes/remove/{id}',  [App\Http\Controllers\Admin\TaxesController::class, 'remove']);
    Route::post('/taxes/store',  [App\Http\Controllers\Admin\TaxesController::class, 'store']);


    Route::post('/inventory/start_inventory',  [App\Http\Controllers\Admin\RequestController::class, 'startInventory']);
    Route::post('/inventory/update_ebay_listings',  [App\Http\Controllers\Admin\RequestController::class, 'updateEbayListings']);

    Route::get('/ebay/template/{slug}', [App\Http\Controllers\Admin\AdminController::class, 'showTemplate']);
    Route::get('/ebay/listings/{ebay_id}',  [App\Http\Controllers\Admin\ListingsController::class, 'show'])->name('ebay.listing');
});


Route::middleware(['auth:sanctum', 'verified'])->prefix('admin')->post('/getSuggestedCategories',  [App\Http\Controllers\Admin\EbayController::class, 'getSuggestedCategories'])->name('getSuggestedCategories');
Route::get('/create-payment-intent', function (Request $request) {
    $orders = Order::where('user_id', 1)->where('status', 'created');
    if ($orders->exists()) {
        foreach ($orders->get() as $order) {
            $stripe = new \Stripe\StripeClient(
                getenv('STRIPE_SECRET')
            );
            $intent = $stripe->paymentIntents->retrieve($order->stripe_id);
            if ($intent->amount == $intent->amount_received) {
                $order->status = 'processing';
                $order->save();
                Mail::to('nagiev.axioma@gmail.com')->send(new OrderConfirmation($order->id));
            }
        }
    }
    else return $orders->get();
});
Route::get('/test', function (Request $request) {
    return Order::with('products')->paginate(10);
});
