<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ProductImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        //$file = $request->file('csv-import');
        $path = storage_path() . '/app/csv-import/import.csv';
        Excel::queueImport(new ProductImport, $path);
        return redirect()->back()->with('success', $path);
    }
}
