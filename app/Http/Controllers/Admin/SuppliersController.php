<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Shop;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;

class SuppliersController extends Controller
{
    public function index() {
        $suppliers = array(
            'lkq_cost_sp'   => Setting::firstOrCreate(['key' => 'lkq_cost_sp'], ['value' => '0'])->value,
            'lkq_cost_mp'   => Setting::firstOrCreate(['key' => 'lkq_cost_mp'], ['value' => '0'])->value,
            'lkq_cost_lp'   => Setting::firstOrCreate(['key' => 'lkq_cost_lp'], ['value' => '0'])->value,
            'lkq_cost_lt'   => Setting::firstOrCreate(['key' => 'lkq_cost_lt'], ['value' => '0'])->value,
            'lkq_cost_additional_sp'   => Setting::firstOrCreate(['key' => 'lkq_cost_additional_sp'], ['value' => '0'])->value,
            'lkq_cost_additional_mp'   => Setting::firstOrCreate(['key' => 'lkq_cost_additional_mp'], ['value' => '0'])->value,
        );

        return view('admin.suppliers')->with('suppliers', $suppliers);
    }

    /**
     * Update LKQ Settings.
     *
     * @param Request $request
     * @return Redirector|Application|RedirectResponse
     */
    public function updateLKQ(Request $request): Redirector|Application|RedirectResponse
    {
        Setting::where('key', 'lkq_cost_sp')->update(['value' => $request->input('lkq_cost_sp')]);
        Setting::where('key', 'lkq_cost_mp')->update(['value' => $request->input('lkq_cost_mp')]);
        Setting::where('key', 'lkq_cost_lp')->update(['value' => $request->input('lkq_cost_lp')]);
        Setting::where('key', 'lkq_cost_lt')->update(['value' => $request->input('lkq_cost_lt')]);
        Setting::where('key', 'lkq_cost_additional_sp')->update(['value' => $request->input('lkq_cost_additional_sp')]);
        Setting::where('key', 'lkq_cost_additional_mp')->update(['value' => $request->input('lkq_cost_additional_mp')]);

        return redirect('/admin/settings/suppliers/');
    }
}
