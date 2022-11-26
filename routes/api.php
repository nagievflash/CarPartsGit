<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Models\Address;
use App\Models\Category;
use App\Models\Compatibility;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\Warehouse;
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
    return Category::all();
});

Route::get('/categories/{title}', function ($title) {
    $title = urldecode($title);
    return Product::select('products.id as id','sku', 'title', 'partslink', 'oem_number', 'price', 'qty', 'images','mcat_name','mscat_name', 'categories.part_name as part_name')
        ->join('categories', 'products.title', '=', 'categories.part_name')
        ->where(function($query) use ($title)
        {
            $query->where('categories.mcat_name', '=', $title)
                ->orWhere('categories.mscat_name', '=', $title)
                ->orWhere('categories.part_name', '=', $title);
        })
        ->hasFitments()
        ->paginate(16);
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

Route::post('/auth/register', [AuthController::class, 'createUser']);
Route::post('/auth/login', [AuthController::class, 'loginUser']);

Route::get('/user/setup-intent',  [App\Http\Controllers\Api\UserController::class, 'getSetupIntent']);
Route::post('/user/payments',  [App\Http\Controllers\Api\UserController::class, 'postPaymentMethods']);

Route::get('/create-payment-intent', function (Request $request) {
    $request->user()->createSetupIntent();
    $payment = $request->user()->payWith(
        10000, ['card', 'paypal']
    );
    return $payment->client_secret;

})->middleware('auth:sanctum');


Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::get('/profile', function (Request $request) {
        return $request->user();
    });

    Route::get('/checkout/intent', [CheckoutController::class, 'intent']);
    Route::post('/checkout/pay', [CheckoutController::class, 'pay']);

    Route::post('/orders/add', function (Request $request) {
        $data = $request->all();
        $user = $request->user();

        $order = Order::create([
            'user_id'       => $user->id,
            'status'        => 'created'
        ]);

        $total = 0;
        $qty = 0;
        $shipping = 0;
        $handling = 0;
        foreach ($data["items"] as $item) {
            $product = Warehouse::where('sku', $item["sku"])->where('supplier_id', 1)->first();
            $order->products()->attach($item["sku"],  ['qty' => $item["quantity"], 'price' => $product->price, 'total' => $product->price * $item["quantity"]]);
            $total += $item['quantity'] * $product->price;
            $shipping += $item['quantity'] * $product->shipping;
            $handling += $item['quantity'] * $product->handling;
            $qty += $item["quantity"];
        }
        $total = $total + $shipping + $handling;
        $total = $total + $total / 4;
        $payment = $user->payWith(
            number_format((float)$total, 2, '.', '') * 100, ['card']
        );

        $address = Address::firstOrCreate([
            'address'   => $data["userdata"]["address"],
            'address2'  => $data["userdata"]["address2"],
            'city'      => $data["userdata"]["city"],
            'zipcode'   => $data["userdata"]["zipcode"],
        ]);

        $user->addresses()->syncWithoutDetaching($address->id);

        $order->total_quantity = $qty;
        $order->total = $total;
        $order->stripe_secret = $payment->client_secret;
        $order->stripe_id = $payment->id;
        $order->addresses()->attach($address->id);
        $order->save();

        return $order->id;
    });

    Route::get('/orders/get', function (Request $request) {
        $user = $request->user();
        return Order::where('user_id', $user->id)->with('products')->with('addresses')->orderBy('id', 'DESC')->paginate(10);
    });

    Route::get('/orders/get/{id}', function (Request $request) {
        $user = $request->user();
        $order = Order::where('user_id', $user->id)->firstOrFail();

        return Order::where('user_id', $user->id)->where('id', $request->id)->with('products')->with('addresses')->firstOrFail()->toJson(JSON_PRETTY_PRINT);
    });

    Route::get('/profile/addresses', function (Request $request) {
        $user = $request->user();
        return User::where('id', $user->id)->first()->addresses()->get();
    });
});
