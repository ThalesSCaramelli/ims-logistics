<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Client;
use App\Models\ClientHourlyRate;
use App\Models\ClientContainerAdditional;
use App\Models\SpecialDay;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products    = Product::orderBy('name')->get();
        $specialDays = SpecialDay::where('is_active', true)->orderBy('date')->get();
        $clients     = Client::where('is_active', true)
            ->with([
                'containerPrices.product',
                'hourlyRates',
                'containerAdditionals',
            ])
            ->orderBy('name')
            ->get();

        return view('products.index', compact('products', 'clients', 'specialDays'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:products,name',
            'code' => 'nullable|string|max:10',
            'type' => 'required|in:container,hour,mixed',
        ]);

        Product::create([
            'name'      => $request->name,
            'code'      => strtoupper($request->code ?? ''),
            'type'      => $request->type,
            'is_active' => true,
        ]);

        return back()->with('success', 'Product added.');
    }

    public function toggleActive(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        return back()->with('success', 'Product ' . ($product->is_active ? 'activated' : 'deactivated') . '.');
    }

    public function saveContainerPrices(Request $request, Client $client)
    {
        $client->containerPrices()->delete();

        foreach ($request->prices ?? [] as $data) {
            if (!isset($data['client_rate']) || $data['client_rate'] === '') continue;

            $hasBox   = !empty($data['has_box_additional']);
            $hasSkill = !empty($data['has_skill_additional']);

            $client->containerPrices()->create([
                'feet'                      => $data['feet'],
                'product_id'                => $data['product_id'] ?: null,
                'client_rate'               => $data['client_rate'],
                'worker_rate'               => $data['worker_rate'] ?? 0,
                // Box additional
                'has_box_additional'        => $hasBox,
                'box_threshold'             => $hasBox ? ($data['box_threshold'] ?? null) : null,
                'box_block_size'            => $hasBox ? ($data['box_block_size'] ?? null) : null,
                'box_client_rate_per_block' => $hasBox ? ($data['box_client_rate_per_block'] ?? 0) : 0,
                'box_worker_rate_per_block' => $hasBox ? ($data['box_worker_rate_per_block'] ?? 0) : 0,
                // Skill additional
                'has_skill_additional'        => $hasSkill,
                'skill_threshold'             => $hasSkill ? ($data['skill_threshold'] ?? null) : null,
                'skill_block_size'            => $hasSkill ? ($data['skill_block_size'] ?? null) : null,
                'skill_client_rate_per_block' => $hasSkill ? ($data['skill_client_rate_per_block'] ?? 0) : 0,
                'skill_worker_rate_per_block' => $hasSkill ? ($data['skill_worker_rate_per_block'] ?? 0) : 0,
                'is_active'                 => true,
            ]);
        }

        return back()->with('success', 'Container prices saved for ' . $client->name . '.');
    }

    public function saveHourlyRates(Request $request, Client $client)
    {
        foreach ($request->hourly ?? [] as $type => $data) {
            if (empty($data['client_rate_per_hour']) && empty($data['worker_rate_per_hour'])) continue;

            ClientHourlyRate::updateOrCreate(
                ['client_id' => $client->id, 'service_type' => $type],
                [
                    'client_rate_per_hour' => $data['client_rate_per_hour'] ?? 0,
                    'worker_rate_per_hour' => $data['worker_rate_per_hour'] ?? 0,
                    'holiday_multiplier'   => $data['holiday_multiplier'] ?? 1.00,
                    'is_active'            => true,
                ]
            );
        }

        return back()->with('success', 'Hourly rates saved for ' . $client->name . '.');
    }

    public function saveContainerAdditionals(Request $request, Client $client)
    {
        ClientContainerAdditional::where('client_id', $client->id)->delete();

        foreach ($request->additionals ?? [] as $i => $data) {
            if (empty($data['name'])) continue;

            ClientContainerAdditional::create([
                'client_id'   => $client->id,
                'name'        => $data['name'],
                'feet'        => $data['feet'] ?? 'both',
                'client_rate' => $data['client_rate'] ?? 0,
                'worker_rate' => $data['worker_rate'] ?? 0,
                'is_active'   => true,
                'sort_order'  => $i,
            ]);
        }

        return back()->with('success', 'Container additionals saved for ' . $client->name . '.');
    }
}