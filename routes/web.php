<?php

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


Route::middleware(['auth:sanctum', 'verified'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::get('/import',  [App\Http\Controllers\Admin\AdminController::class, 'importProductsFromCSV'])->name('import');
    Route::get('/import/products/custom',  [App\Http\Controllers\Admin\AdminController::class, 'ebayUpload'])->name('ebayUpload');
    Route::get('/categories',  [App\Http\Controllers\Admin\CategoriesController::class, 'createCategoriesFromJson'])->name('categoriesList');
    Route::get('/products',  [App\Http\Controllers\Admin\ProductsController::class, 'index'])->name('products.list');
    Route::get('/ebay/listings',  [App\Http\Controllers\Admin\AdminController::class, 'ebayListings'])->name('ebay.listings');

    Route::post('/import',  [App\Http\Controllers\Admin\RequestController::class, 'importProductsBasics']);
    Route::post('/import/products/custom',  [App\Http\Controllers\Admin\RequestController::class, 'importProductsCustom']);
    Route::get('/ebay/upload',  [App\Http\Controllers\Admin\EbayController::class, 'addFixedPriceItem']);
    Route::get('/ebay/revise',  [App\Http\Controllers\Admin\EbayController::class, 'reviseFixedPriceItem']);
});


Route::middleware(['auth:sanctum', 'verified'])->prefix('admin')->post('/getSuggestedCategories',  [App\Http\Controllers\Admin\EbayController::class, 'getSuggestedCategories'])->name('getSuggestedCategories');
