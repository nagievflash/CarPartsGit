<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function createCategoriesFromJson() {
        $catTree = json_decode(file_get_contents(resource_path() . "/json/categories.json"), true);
        dd($catTree['rootCategoryNode']);
    }
}
