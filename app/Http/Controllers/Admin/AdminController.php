<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function importProductsFromCSV(Request $request) {
        $code = $request->has('code') ? $request->input('code') : '';
        return view('importCSVItem')->with('code', $code);
    }
}
