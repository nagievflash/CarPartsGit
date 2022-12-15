<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
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
use App\Mail\ThanksForJoining;
use App\Models\PendingReceipt;

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
    $title = $request->title;
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
        $user = $request->user();
        Mail::to($user->email)->send(new ThanksForJoining());

        return response()->json(['message' => 'Email successfully verified'], 200);
    }catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 422);
    }
})->middleware(['auth:sanctum', 'signed'])->name('verification.verify');

Route::group(['middleware' => ['auth:sanctum']], function () {

//    Route::post('/subscribe-product', function (Request $request) {
//        $user = $request->user();
//
//        if(!PendingReceipt::where([['email',$user->email] , ['id',$request->product_id]])->exists()){
//            PendingReceipt::insert(
//                [
//                    'email' => $user->email,
//                    'product_id' => $request->product_id
//                ]
//            );
//            return response()->json(['message' => 'You have successfully subscribed to the product update'], 200);
//        }else{
//            return response()->json(['message' => 'You are already subscribed to updates for this product'], 422);
//        }
//    });

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

    Route::put('/profile/update',[UserController::class, 'profileUpdate']);

    Route::put('/profile/update/password', [UserController::class, 'updatePassword']);

    Route::get('/checkout/intent', [CheckoutController::class, 'intent']);
    Route::post('/checkout/pay', [CheckoutController::class, 'pay']);

    Route::post('/orders/add', [OrderController::class, 'store']);

    Route::get('/orders/get', [OrderController::class, 'index']);

    Route::get('/orders/get/{id}', [OrderController::class, 'show']);

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
        $address = $user->addresses()->findOrFail($id);
        $address->update([
            'address' => $request->address ?? '',
            'address2' => $request->address2 ?? '',
            'city'     => $request->city ?? '',
            'state'    => $request->state ?? '',
            'zipcode'  => $request->zipcode ?? '',
            'country'  => $request->country ?? '',
        ]);
        return $address->toJson(JSON_PRETTY_PRINT);
    });

    Route::post('/profile/addresses/create', function (Request $request) {
        try {
            $user = $request->user();

            $address = Address::firstOrCreate([
                'country'   => $request->country ?? '',
                'state'     => $request->state ?? '',
                'address'   => $request->address ?? '',
                'address2'  => $request->address2 ?? '',
                'city'      => $request->city ?? '',
                'zipcode'   => $request->zipcode ?? '',
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
