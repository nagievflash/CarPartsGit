<?php
namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Creates an intent for payment so we can capture the payment
     * method for the user.
     *
     * @param Request $request The request data from the user.
     */
    public function getSetupIntent( Request $request ){
        return $request->user()->createSetupIntent();
    }

    /**
     * Adds a payment method to the current user.
     *
     * @param Request $request The request data from the user.
     */
    public function postPaymentMethods( Request $request ): JsonResponse
    {
        $user = $request->user();
        $paymentMethodID = $request->get('payment_method');

        if( $user->stripe_id == null ) {
            $user->createAsStripeCustomer();
        }

        $user->addPaymentMethod( $paymentMethodID );
        $user->updateDefaultPaymentMethod( $paymentMethodID );

        return response()->json( null, 204 );
    }

    /**
     * Update User's profile method
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function profileUpdate(Request $request): JsonResponse
    {

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

        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Update User's password method
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updatePassword(Request $request): JsonResponse
    {
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

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }


}
