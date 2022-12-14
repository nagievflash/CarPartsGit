<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmation;
use App\Models\Address;
use App\Models\Backlog;
use App\Models\Order;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller {

    public function index(Request $request) {
        $user = $request->user();
        return Order::where('user_id', $user->id)
            ->with('products')
            ->with('addresses'
            )->orderBy('id', 'DESC')
            ->paginate(10);
    }

    public function show(Request $request) {
        $user = $request->user();
        return Order::where('user_id', $user->id)
            ->where('id', $request->id)
            ->with('products')
            ->with('addresses')
            ->firstOrFail()
            ->toJson(JSON_PRETTY_PRINT);
    }

    public function store(Request $request) {

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
            $order->products()->attach($item["sku"],  ['qty' => $item["quantity"], 'price' => ($product->price + $product->price / 4) , 'total' => ($product->price + $product->price / 4) * $item["quantity"]]);
            $total += $item['quantity'] * ($product->price + $product->price / 4);
            $shipping += $item['quantity'] * ($product->shipping + $product->shipping / 4);
            $handling += $item['quantity'] * ($product->handling + $product->handling / 4);
            $qty += $item["quantity"];
        }
        $subtotal = $total;
        $total = $total + $shipping + $handling;
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
        $order->total = $subtotal;
        $order->shipping = $shipping;
        $order->handling = $handling;
        $order->stripe_secret = $payment->client_secret;
        $order->stripe_id = $payment->id;
        $order->addresses()->attach($address->id);
        $order->save();

        Mail::to($user->email)->send(new OrderConfirmation($order->id));

        return $order->id;
    }
}
