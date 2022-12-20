<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\Cancellations;
use Exception;

class OrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(Request $request): View|Factory|Application
    {
        $orders = Order::all();
        return view('admin.orders')->with('products', $orders);
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Factory|View|Application
     */
    public function show(string $id): Factory|View|Application
    {
        $order = Order::findOrFail('id', $id);
        return view('admin.order')->with('order', $order);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     */
    public function edit(int $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     */
    public function update(Request $request, int $id)
    {
        try {
            Order::where('id', $id)->update([
                'status' => $request->status
            ]);
            return response()->json(['message' => 'Order successfully modified'], 200);
        }catch (Exception $e){
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     */
    public function destroy(int $id)
    {
        try {
            Order::where('id', $id)->delete();
            Mail::to(auth()->user()->email)->send(new Cancellations());
            return response()->json(['message' => 'Order successfully deleted!'], 200);
        }catch (Exception $e){
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
