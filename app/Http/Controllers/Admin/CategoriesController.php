<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\CategoriesImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class CategoriesController extends Controller
{

    public function categoriesImport(Request $request): \Illuminate\Http\RedirectResponse
    {
        $file = $request->file('import');
        Storage::disk('local')->putFileAs('/files/', $file, 'category.csv');
        Excel::queueImport(new CategoriesImport, storage_path().'/app/files/category.csv');

        return redirect()->back()->with('success', 'Categories uploaded successful');
    }
}
