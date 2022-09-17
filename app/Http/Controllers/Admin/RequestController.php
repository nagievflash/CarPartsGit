<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\CustomProductsImport;
use App\Imports\FitmentImport;
use App\Imports\InventoryImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class RequestController extends Controller
{
    /**
     * Imports basic products
     * @param Request $request
     * @return RedirectResponse
     */
    public function importProductsBasics(Request $request): RedirectResponse
    {
        $file = $request->file('csv-import');

        Storage::disk('local')->putFileAs('/files/', $file, 'inventory.csv');

        Excel::queueImport(new InventoryImport, storage_path().'/app/files/inventory.csv');
        return redirect()->back()->with('success', 'The Job started successfully!');
    }

    /**
     * Imports products from file and upload to ebay
     * @param Request $request
     * @return RedirectResponse
     */
    public function importProductsCustom(Request $request): RedirectResponse
    {
        $file = $request->file('csv-import');

        Storage::disk('local')->putFileAs('/files/', $file, 'productsCustom.csv');
        $shop = $request->input('shop');
        Excel::queueImport(new CustomProductsImport($shop), storage_path().'/app/files/productsCustom.csv');
        return redirect()->back()->with('success', 'The Job started successfully!');
    }

    /**
     * Imports fitments from file
     * @param Request $request
     * @return RedirectResponse
     */
    public function importFitments(Request $request): RedirectResponse
    {
        $file = $request->file('csv-import');

        //Storage::disk('local')->putFileAs('/files/', $file, 'fitments.csv');
        Excel::queueImport(new FitmentImport(), storage_path().'/app/files/fitments.csv');
        return redirect()->back()->with('success', 'The Job started successfully!');
    }
}
