<?php

namespace App\Http\Controllers;

use App\Models\Backlog;
use Illuminate\Http\Request;

class CheckoutController extends Controller {

    /**
     * Setup payment intent for this user
     *
     */
    public function intent(Request $request)
    {
        $user = $request->user();

        $payment = $user->payWith(
            10000, ['card']
        );
        return response()->json(
            [
                'clientSecret' => $payment->client_secret,
                'amount' => 10000
            ]
        );
    }

    /**
     * Charge the user's card
     *
     */
    public function pay(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $paymentMethod = $request->payment_method;
        $address = $request->billing_address;
        $amount = $request->amount;

        try {
            $user->createOrGetStripeCustomer();
            $user->updateDefaultPaymentMethod($paymentMethod);
            $amount = $amount * 100; //convert to ,etc
            $payment = $user->charge($amount, $paymentMethod);

            if ($payment->status === 'succeeded') {
                Backlog::createBacklog('payment messages', 'success payed ' . $paymentMethod);
            }

            return response()->json(
                ['status' => 'success', 'data' => ['payment' => $payment]]
            );
        } catch (\Throwable $th) {
            throw $th;
        }
    }

}
