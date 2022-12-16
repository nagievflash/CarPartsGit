<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmation;
use App\Models\Address;
use App\Models\Backlog;
use App\Models\Order;
use App\Models\Product;
use App\Models\Tax;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller {

    public function index(Request $request) {
        $user = $request->user();
        $orders = Order::where('user_id', $user->id)->where('status', 'created');
        if ($orders->exists()) {
            foreach ($orders->get() as $order) {
                $this->checkStripeOrderStatus($order, $request);
            }
            return Order::where('user_id', $user->id)
                ->with('products')
                ->with('addresses'
                )->orderBy('id', 'DESC')
                ->paginate(10);
        }
        else return $orders->get();
    }

    public function show(Request $request) {
        $user = $request->user();
        $order = Order::where('user_id', $user->id)->where('id', $request->id)->firstOrFail();
        if ($order->status == 'created' ) {
            $this->checkStripeOrderStatus($order, $request);
        }
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
            $product = Product::where('sku', $item["sku"])->first();
            $order->products()->attach($item["sku"],  ['qty' => $item["quantity"], 'price' => $product->price , 'total' => $product->price * $item["quantity"]]);
            $total += $item['quantity'] * $product->price;
            $shipping += $item['quantity'] * $product->shipping;
            $handling += $item['quantity'] * $product->handling;
            $qty += $item["quantity"];
        }
        $subtotal = $total;
        $total = $total + $shipping + $handling;
        $tax = $total * Tax::where('state', $data["userdata"]["state"])->first()->rate / 100;
        $total += $tax;
        $stripe = new \Stripe\StripeClient(
            getenv('STRIPE_SECRET')
        );
        $payment =  $stripe->paymentIntents->create([
            'amount' => number_format((float)$total, 2, '.', '') * 100,
            'currency' => 'usd',
            'automatic_payment_methods' => [
                'enabled' => 'true',
            ],
            'metadata' => [
                "order_id" => $order->id,
                'shipping'  => $shipping,
                'handling'  => $handling,
                'tax'       => $tax
            ],
            'shipping' => [
                'address' => [
                    'country'       => $data["userdata"]["country"],
                    'state'         => $data["userdata"]["state"],
                    'line1'         => $data["userdata"]["address"],
                    'line2'         => $data["userdata"]["address2"],
                    'city'          => $data["userdata"]["city"],
                    'postal_code'   => $data["userdata"]["zipcode"],
                ],
                'name'  => $user->name,
                'phone' => $user->phone
            ]
        ]);

        $user->name = $data["userdata"]["firstname"];
        $user->lastname = $data["userdata"]["lastname"];
        $user->phone = $data["userdata"]["phone"];
        $user->save();

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
        $order->tax = $tax;
        $order->shipping = $shipping;
        $order->handling = $handling;
        $order->stripe_secret = $payment->client_secret;
        $order->stripe_id = $payment->id;
        $order->addresses()->attach($address->id);
        $order->save();

        return $order->id;
    }

    public function checkStripeOrderStatus(Order $order, Request $request) {
        $stripe = new \Stripe\StripeClient(
            getenv('STRIPE_SECRET')
        );
        $intent = $stripe->paymentIntents->retrieve($order->stripe_id);
        if ($intent->amount == $intent->amount_received) {
            $order->status = 'processing';
            $order->save();
            $user = $request->user();
            Mail::to($user->email)->send(new OrderConfirmation($order->id));
        }

    }
}
