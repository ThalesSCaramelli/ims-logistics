<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Client;
use App\Models\ClientHourlyRate;
use App\Models\ClientPriceHistory;
use App\Models\SpecialDay;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products    = Product::orderBy('name')->get();
        $specialDays = SpecialDay::where('is_active', true)->orderBy('date')->get();
        $clients = Client::orderBy('name')->get(); // <-- ADICIONAR

        return view('products.index', compact('products', 'specialDays', 'clients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:products,name',
            'code' => 'nullable|string|max:10',
            'type' => 'required|in:container,hour,mixed',
        ]);

        Product::create([
            'name'             => $request->name,
            'code'             => strtoupper($request->code ?? ''),
            'type'             => $request->type,
            'has_boxes_skills' => $request->type === 'container',
            'is_default'       => false,
            'is_active'        => true,
        ]);

        return back()->with('success', 'Product added.');
    }

    public function update(Request $request, Product $product)
    {
        $request->validate(['name' => 'required|string|max:100|unique:products,name,'.$product->id]);
        $product->update(['name' => $request->name, 'code' => strtoupper($request->code ?? '')]);
        return back()->with('success', 'Product updated.');
    }

    public function toggleActive(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        return back()->with('success', 'Product '.($product->is_active ? 'activated' : 'deactivated').'.');
    }
}
