<?php

use App\Http\Controllers\Api\AuthController;
use App\Models\Compatibility;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\Year;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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


Route::get('/products', function (Request $request) {
    if ($request->has('search')) {
        $products =  Product::where("sku", $request->get("search"))
            ->orWhere("partslink", 'like',  '%'.$request->get("search").'%')
            ->orWhere("oem_number", 'like',  '%'.$request->get("search").'%')
            ->hasFitments()->isAvailable()->paginate(16);
    }
    else $products = Product::hasFitments()->isAvailable()->paginate(16);
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
    return DB::table('filters')
        ->select('year')
        ->orderBy('year', 'desc')
        ->distinct()
        ->get();
});

Route::get('/make/{year}', function ($year) {
    return DB::table('filters')
        ->select('make_name')
        ->where('year', $year)->orderBy('make_name')
        ->distinct()->get();
});

Route::get('/model/{year}/{make}', function ($year, $make) {
    return DB::table('filters')
        ->select('model_name')
        ->where('year', $year)
        ->where('make_name', $make)
        ->orderBy('model_name')
        ->distinct()->get();
});

Route::get('/submodel/{year}/{make}/{model}', function ($year, $make, $model) {
    return DB::table('filters')
        ->select('submodel_name', 'part_name')
        ->where('year', $year)
        ->where('make_name', $make)
        ->where('model_name', $model)
        ->where('submodel_name', '!=', '')
        ->orderBy('submodel_name')
        ->distinct()->get();
});

Route::get('/categories/{year}/{make}/{model}/{submodel?}', function ($year, $make, $model, $submodel) {
    if ($submodel && $submodel != 0) {
        return DB::table('filters')
            ->select('part_name')
            ->where('year', $year)
            ->where('make_name', $make)
            ->where('model_name', $model)
            ->where('submodel_name', $submodel)
            ->orderBy('part_name')
            ->distinct()->get();
    }
    else {
        return DB::table('filters')
            ->select('part_name')
            ->where('year', $year)
            ->where('make_name', $make)
            ->where('model_name', $model)
            ->orderBy('part_name')
            ->distinct()->get();
    }
});


Route::get('/filter/{year}/{make}/{model}/{submodel}/{category}', function ($year, $make, $model, $submodel, $category) {
    if ($submodel != 0) {
        return Product::whereHas('fitments', function ($query) use ($category, $submodel, $year, $make, $model) {
            return $query->where('year', '=', $year)
                ->where('make_name', $make)
                ->where('model_name', $model)
                ->where('submodel_name', $submodel)
                ->where('part_name', $category);
        })->isAvailable()->paginate(16);
    }
    else {
        return Product::whereHas('fitments', function ($query) use ($category, $year, $make, $model) {
            return $query->where('year', '=', $year)
                ->where('make_name', $make)
                ->where('model_name', $model)
                ->where('part_name', $category);
        })->isAvailable()->paginate(16);
    }
});

Route::get('/categories-list/', function () {
    return DB::table('compatibilities')
        ->select('part_name')
        ->orderBy('part_name')
        ->distinct()
        ->get();
});

Route::get('/categories/{title}', function ($title) {
    return Product::whereHas('fitments', function ($query) use ($title) {
        return $query->where('part_name', '=', $title);
    })->isAvailable()->paginate(16);
});


Route::post('/orders/add', function (Request $request) {
    $data = $request->all();
    $user = User::updateOrCreate([
        'email' => $data["userdata"]["email"]
    ],[
        'email'         => $data["userdata"]["email"],
        'name'          => $data["userdata"]["firstname"],
        'lastname'      => $data["userdata"]["lastname"],
        'address'       => $data["userdata"]["address"],
        'address2'      => $data["userdata"]["address2"],
        'city'          => $data["userdata"]["city"],
        'zipcode'       => $data["userdata"]["zipcode"],
        'password'      => md5("password"),
    ]);

    $order = Order::create([
        'user_id'       => $user->id,
        'status'        => 'created'
    ]);

    foreach ($data["items"] as $item) {
        $order->products()->attach($item["sku"],  ['qty' => $item["quantity"]]);
    }

    return $order->id;
});


Route::get('/oauth2/authorize', function (Request $request) {
    $code = $request->input('code');
    try {
        $oauthToken = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . base64_encode(env('EBAY_APP_ID').':'.env('EBAY_SECRET')),
        ])->send('POST', 'https://api.ebay.com/identity/v1/oauth2/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => env('EBAY_RUNAME'),
            ]
        ]);
        $code = $oauthToken->json()['refresh_token'];
        Setting::setAccessToken($code);

    } catch (\Exception $e) {
        abort(404);
    }
    return 'Successful updated code: ' . $code;
});


// AUTHORIZATION

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/auth/register', [AuthController::class, 'createUser']);
Route::post('/auth/login', [AuthController::class, 'loginUser']);
