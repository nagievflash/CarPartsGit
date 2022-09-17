<?php

use App\Models\Compatibility;
use App\Models\Product;
use App\Models\Year;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/products', function (Request $request) {
    if ($request->has('search')) {
        $products = Product::where("sku", $request->get("search"))->exists()
            ? Product::where("sku", $request->get("search"))->paginate(15)
            : Product::where("partslink", $request->get("search"))->paginate(15);
    }
    else $products = Product::paginate(15);
    return $products;
});

Route::get('/fitments', function (Request $request) {
    if ($request->has('sku')) {
        return Compatibility::where('sku', $request->input('sku'))->paginate();
    }
    else return response(['error' => true, 'error-msg' => 'Parameter sku is empty'],404);
});


Route::get('/products/{sku}', function ($sku) {
    $product = Product::with('attributes')->with('fitments')->where('sku', $sku);
    return $product->exists() ? $product->first() : response(['error' => true, 'error-msg' => 'Product not found'],404);
});

Route::get('/years', function () {
    return DB::table('years')->select('title')->orderBy('title', 'desc')->get();
});

Route::get('/year/{year}', function ($year) {
    return DB::table('fitments')
        ->select('make_name', 'model_name', 'submodel_name')
        ->where('year', $year)->orderBy('make_name')
        ->distinct()->get()->groupBy('make_name');
});

Route::get('/filter/{year}/{make}/{model}', function ($year, $make, $model) {
    return Product::whereHas('fitments', function ($query) use ($year, $make, $model) {
        return $query->where('year', '=', $year)->where('make_name', $make)->where('model_name', $model);
    })->paginate(16);
});

