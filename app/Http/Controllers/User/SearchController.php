<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {

        $request->validate([
            'search' => 'required|string',
            'type' => 'required|in:product,brand,shop'
        ]);
    }
}
