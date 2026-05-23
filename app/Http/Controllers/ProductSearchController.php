<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductSearchController extends Controller
{
    public function search(Request $request)
    {
        $term = $request->get('q');

        $products = Product::where('status', ACTIVE_STATUS)
            ->where('stock', '>', 0)
            ->where('name', 'like', "%{$term}%")
            ->orderBy('name')
            ->get();

        $results = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'text' => $product->name . ' (' . $product->type . ')'
            ];
        });

        return response()->json($results);
    }
}
