<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Mail\OrderConfirmation;
use App\Mail\ResetPassword;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\Address;
use App\Models\Category;
use App\Models\Compatibility;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\State;
use App\Models\User;
use App\Models\Rates;
use App\Models\Warehouse;
use App\Models\Year;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Password;
use App\Mail\Feedback;
use App\Models\Tickets;
use Illuminate\Support\Facades\Storage;
use App\Mail\Ticket;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

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
        $products = Product::where("sku", $request->get("search"))
            ->orWhere("partslink", 'like', '%'.$request->get("search").'%')
            ->orWhere("oem_number", 'like', '%'.$request->get("search").'%')
            ->hasFitments()->isAvailable();
    }
    else $products = Product::hasFitments()->isAvailable();

    $paginate = $request->has('paginate') ? (int)$request->get("paginate") : 16;
    $sort = $request->has('sort') && in_array($request->get('sort'),['price','created_at']) ? $request->get('sort') : 'price';
    $orderBy = $request->has('orderBy') && in_array(strtolower($request->get('orderBy')),['desc','asc']) ? $request->get('orderBy') : 'asc';

    return $products->orderBy($sort, $orderBy)->paginate($paginate);
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
/*        return Product::whereHas('fitments', function ($query) use ($category, $submodel, $year, $make, $model) {
            return $query->where('year', '=', $year)
                ->where('make_name', $make)
                ->where('model_name', $model)
                ->where('submodel_name', $submodel)
                ->where('part_name', $category);
        })->isAvailable()->paginate(16);*/
        return Product::hasFitments()
            ->join('fitments', 'products.sku', '=', 'fitments.sku')
            ->where('year', '=', $year)
            ->where('make_name', $make)
            ->where('model_name', $model)
            ->where('submodel_name', $submodel)
            ->where('part_name', $category)
            //->isAvailable()
            ->paginate(16);
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

Route::get('/categories/{title}', function (Request $request) {
    $title = explode('/',$request->path());
    $title = array_pop($title);

    $paginate = $request->has('paginate') ? (int)$request->get("paginate") : 16;
    $sort = $request->has('sort') && in_array($request->get('sort'),['price','created_at']) ? $request->get('sort') : 'price';
    $orderBy = $request->has('orderBy') && in_array(strtolower($request->get('orderBy')),['desc','asc']) ? $request->get('orderBy') : 'asc';

    return Product::select('products.id as id','sku', 'title', 'partslink', 'oem_number', 'price', 'qty', 'images','mcat_name','mscat_name', 'categories.part_name as part_name')
        ->join('categories', 'products.title', '=', 'categories.part_name')
        ->where(function($query) use ($title)
        {
            $query->where('categories.mcat_name', '=', $title)
                ->orWhere('categories.mscat_name', '=', $title)
                ->orWhere('categories.part_name', '=', $title);
        })
        ->hasFitments()
        ->orderBy($sort, $orderBy)->paginate($paginate);
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

Route::post('/profile/validate', function (Request $request) {

    if ($request->has('email')) {
        $request->validate([
            'email' => 'filled|regex:/(.+)@(.+)\.(.+)/i|unique:users,email',
        ]);
    }

    if ($request->has('phone')) {
        $request->validate([
            'phone' => 'filled|phone|unique:users,phone|size:11',
        ]);
    }
});

Route::post('/profile/reset', function (Request $request) {

    $request->validate(['email' => 'required|email']);

    try {
        $user = User::where('email', $request->only('email'))->first();
        if ($user) {
            //so we can have dependency
            $password_broker = app(PasswordBroker::class);
            //create reset password token
            $token = $password_broker->createToken($user);

            DB::table('password_resets')->insert(['email' => $user->email, 'token' => $token, 'created_at' => new Carbon]);

            Mail::to($user->email)->send(new ResetPassword($token,$user->email));

            return response()->json(['message' => 'successfully!'], 200);
        }else{
            return response()->json(['message' => 'User with this email does not exist!'], 422);
        }
    }catch (Exception $e){
        return response()->json(['message' => $e->getMessage()], 422);
    }

})->middleware('guest')->name('password.email');

//Route::get('/reset-password/{token}', function ($token) {
//    return view('auth.reset-password', ['token' => $token]);
//})->middleware('guest');

Route::post('/create-new-password', function (Request $request) {

    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);

    try {
          Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return response()->json(['message' => 'successfully!'], 200);
    }catch (Exception $e) {
        return response()->json(['message' => $e->getMessage()], 422);
    }

})->name('create.new-password');

Route::get('/user/setup-intent',  [App\Http\Controllers\Api\UserController::class, 'getSetupIntent']);
Route::post('/user/payments',  [App\Http\Controllers\Api\UserController::class, 'postPaymentMethods']);

Route::get('/states', function (Request $request) {
    return State::all();
});


Route::post('/email/verification-notification', function (Request $request) {
    try {
        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Confirmation link sent to email'], 200);
    }catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 422);
    }
})->middleware(['auth:sanctum', 'throttle:6,1'])->name('verification.send');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    try {
        $request->fulfill();
        return response()->json(['message' => 'Email successfully verified'], 200);
    }catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 422);
    }
})->middleware(['auth:sanctum', 'signed'])->name('verification.verify');

Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::post('/products/rate', function (Request $request) {
        try {
            (new Rates)->create(
                [
                    'rate_type' => 'App\Models\Product',
                    'rate_id'   => $request->get("id"),
                    'value'     => $request->get("value"),
                ]
            );
            return response()->json(['message' => 'Your score has been counted!!'], 200);
        }catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    });

    Route::get('/profile', function (Request $request) {
        return $request->user();
    });

    Route::put('/profile/update', function (Request $request) {

        $request->validate([
            'name'      => 'required',
            'lastname'  => 'required',
            'phone'     => 'required',
        ]);

        try {
            $user = $request->user();

            $user->update([
                'name'     => $request->name,
                'lastname' => $request->lastname,
                'phone'    => $request->phone,
                //'profile_photo_path' => $request->profile_photo_path,
            ]);

            return response()->json($request->user(), 200);

        }catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    });

    Route::put('/profile/update/password', function (Request $request) {

        $request->validate([
            'old_password'  => 'current_password',
            'new_password'  => 'required',
        ]);

        try {
            $user = $request->user();
            $password = Hash::make($request->new_password);

            $user->update([
                'password'     => $password,
            ]);

            return response()->json($request->user(), 200);

        }catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
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
        /*        $payment = $user->payWith(
            number_format((float)$total, 2, '.', '') * 100, ['card']
        );*/
        $stripe = new \Stripe\StripeClient(
            getenv('STRIPE_SECRET')
        );
        $payment =  $stripe->paymentIntents->create([
            'amount' => number_format((float)$total, 2, '.', '') * 100,
            'currency' => 'usd',
            'automatic_payment_methods' => [
                'enabled' => 'true',
            ],
        ]);

        $address = Address::firstOrCreate([
            'country'   => $data["userdata"]["country"],
            'state'     => $data["userdata"]["state"],
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

        Mail::to($user->email)->send(new OrderConfirmation($order->id));

        return $order->id;
    });

    Route::get('/orders/get', function (Request $request) {
        $user = $request->user();
        return Order::where('user_id', $user->id)->with('products')->with('addresses')->orderBy('id', 'DESC')->paginate(10);
    });

    Route::get('/orders/get/{id}', function (Request $request) {
        $user = $request->user();
        return Order::where('user_id', $user->id)->where('id', $request->id)->with('products')->with('addresses')->firstOrFail()->toJson(JSON_PRETTY_PRINT);
    });

    Route::get('/profile/addresses', function (Request $request) {
        $user = $request->user();
        return User::where('id', $user->id)->first()->addresses()->get();
    });

    Route::delete('/profile/addresses/delete/{id}', function (Request $request) {
        $user = $request->user();
        $id = $request->id;
        $address = $user->addresses()->findOrFail($id);
        $address->delete();
        return 'success deleted';
    });

    Route::put('/profile/addresses/update/{id}', function (Request $request) {
        $user = $request->user();
        $id = $request->id;
        $data = $request->data;
        $address = $user->addresses()->findOrFail($id);
        $address->update([
            'address1' => $request->address1 ?? '',
            'address2' => $request->address2 ?? '',
            'city'     => $request->city ?? '',
            'state'    => $request->state ?? '',
            'zip'      => $request->zip ?? '',
            'country'  => $request->country ?? '',
        ]);
        return $address->toJson(JSON_PRETTY_PRINT);
    });

    Route::post('/profile/addresses/create', function (Request $request) {
        try {
            $user = $request->user();
            $data = $request->data;

            $address = Address::firstOrCreate([
                'country'   => $data["country"],
                'state'     => $data["state"],
                'address'   => $data["address"],
                'address2'  => $data["address2"],
                'city'      => $data["city"],
                'zip'       => $data["zip"],
            ]);

            $user->addresses()->attach($address->id);
            $user->save();

            return $address->toJson(JSON_PRETTY_PRINT);
        }catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    });

});

Route::put('/feedback', function (Request $request) {
    try {
        $email = $request->email;
        $message = $request->message;

        Mail::to($email)->send(new Feedback($message));

        return response()->json(['message' => 'Application successfully sent!'], 200);
    }catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 422);
    }
});

Route::post('/setTicket', function (Request $request) {

    $request->validate([
        'name'      => 'required',
        'phone'     => 'required',
    ]);

    try {
        $data['email']    = $request->email;
        $data['name']     = $request->name;
        $data['phone']    = $request->phone;
        $data['messages'] = $request->message;
        $data['path']     = '';
        $utm = '';
        if(!empty($request->utm) && is_array($request->utm)){
            foreach ($request->utm as $key => $value){
                $utm .= $key . ': ' . $value . ';';
            }
        }
        $data['utm'] = $utm;
        if(!empty($request->file())){
            $data['path'] = 'https://' . $_SERVER['HTTP_HOST'] . '/' . Storage::putFile('tickets/' . 'ticket_file_' . $data['email'] . rand(0,1000), $request->file('file'));
        }
        $ticket = (new Tickets());
        $ticket->fill($data);
        $ticket->save();

        $data['id'] = $ticket->id;
        $data['created_at'] = $ticket->created_at;

        Mail::to( env('MAIL_USERNAME'))->send(new Ticket($data));

        return response()->json(['message' => 'Application successfully sent!'], 200);
    }catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 422);
    }
});
