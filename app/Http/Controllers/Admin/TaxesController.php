<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tax;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redirect;

class TaxesController extends Controller
{

    /**
     * List all taxes
     *
     * @return Factory|View|Application
     */
    public function index(): Factory|View|Application
    {
        $taxes = Tax::all();
        return view('admin.taxes')->with('taxes', $taxes);
    }


    /**
     * Display the specified tax.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $state  = $request->get('state');
        $rate   = $request->get('rate');
        Tax::updateOrCreate([ 'state' => $state], ['state' => $state, 'rate'  => $rate]);
        return Redirect::back()->with('message', 'Success added!');
    }

    /**
     * Remove the specified tax.
     *
     * @param int $id
     * @return Application|ResponseFactory|Response
     */
    public function remove(int $id): Response|Application|ResponseFactory
    {
        $tax = Tax::firstOrFail('id', $id);
        $tax->delete();
        return response('Success deleted', 200)
            ->header('Content-Type', 'text/plain');
    }
}
