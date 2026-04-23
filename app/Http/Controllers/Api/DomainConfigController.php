<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\Category;
use App\Models\Extension;

class DomainConfigController extends Controller
{
    public function index()
    {
        return response()->json([
            'config' => Config::pluck('config_value', 'config_key'),
            'categories' => Category::all(),
            'extensions' => Extension::with('categories')->get(),
        ]);
    }
}