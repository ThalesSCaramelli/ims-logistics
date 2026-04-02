<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Product;
use App\Models\ClientHourlyRate;
use App\Models\ClientPriceHistory;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::with(['sites'])->orderBy('name')->get();
        return view('clients.index', compact('clients'));
    }

    public function show(Client $client)
    {
        $client->load(['sites', 'containerPrices.product', 'hourlyRates']);

        $products    = Product::where('is_active', true)->orderBy('name')->get();
        $hourlyRates = $client->hourlyRates->keyBy('service_type');

        $priceHistory = ClientPriceHistory::where('client_id', $client->id)
            ->with('changedBy')
            ->orderByDesc('changed_at')
            ->limit(20)
            ->get();

        return view('clients.show', compact(
            'client', 'products', 'hourlyRates', 'priceHistory'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:clients,name',
            'abn'  => 'nullable|string|max:20',
        ]);

        Client::create([
            'name'               => $request->name,
            'abn'                => $request->abn,
            'contact_name'       => $request->contact_name,
            'contact_email'      => $request->contact_email,
            'contact_phone'      => $request->contact_phone,
            'requires_induction' => $request->boolean('requires_induction'),
            'notes'              => $request->notes,
            'is_active'          => true,
        ]);

        return back()->with('success', 'Client added.');
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:clients,name,' . $client->id,
        ]);

        $client->update([
            'name'               => $request->name,
            'abn'                => $request->abn,
            'contact_name'       => $request->contact_name,
            'contact_email'      => $request->contact_email,
            'contact_phone'      => $request->contact_phone,
            'requires_induction' => $request->boolean('requires_induction'),
            'notes'              => $request->notes,
            'is_active'          => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Client updated.');
    }

    // ── Container prices ──────────────────────────────────────────────
    public function saveContainerPrices(Request $request, Client $client)
    {
        $oldData = $client->containerPrices->toArray();

        $client->containerPrices()->delete();

        $seen    = [];
        $newData = [];

        foreach ($request->prices ?? [] as $data) {
            if (empty($data['client_rate'])) continue;

            $key = ($data['feet'] ?? '') . '-' . ($data['product_id'] ?? 'null');
            if (isset($seen[$key])) continue;
            $seen[$key] = true;

            $hasBox   = !empty($data['has_box_additional']);
            $hasSkill = !empty($data['has_skill_additional']);

            $row = [
                'feet'                      => $data['feet'],
                'product_id'                => $data['product_id'] ?: null,
                'client_rate'               => $data['client_rate'],
                'worker_rate'               => $data['worker_rate'] ?? 0,
                'has_box_additional'        => $hasBox,
                'box_threshold'             => $hasBox ? ($data['box_threshold'] ?? null) : null,
                'box_block_size'            => $hasBox ? ($data['box_block_size'] ?? null) : null,
                'box_client_rate_per_block' => $hasBox ? ($data['box_client_rate_per_block'] ?? null) : null,
                'box_worker_rate_per_block' => $hasBox ? ($data['box_worker_rate_per_block'] ?? null) : null,
                'has_skill_additional'        => $hasSkill,
                'skill_threshold'             => $hasSkill ? ($data['skill_threshold'] ?? null) : null,
                'skill_block_size'            => $hasSkill ? ($data['skill_block_size'] ?? null) : null,
                'skill_client_rate_per_block' => $hasSkill ? ($data['skill_client_rate_per_block'] ?? null) : null,
                'skill_worker_rate_per_block' => $hasSkill ? ($data['skill_worker_rate_per_block'] ?? null) : null,
                'is_active'                 => true,
            ];

            $client->containerPrices()->create($row);
            $newData[] = $row;
        }

        $this->logHistory($client, 'container_prices', $oldData, $newData);

        return back()->with('success', 'Container prices saved for ' . $client->name . '.');
    }

    // ── Hourly rates ──────────────────────────────────────────────────
    public function saveHourlyRates(Request $request, Client $client)
    {
        $oldRates = $client->hourlyRates->toArray();
        $newRates = [];

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
            $newRates[] = array_merge(['service_type' => $type], $data);
        }

        $this->logHistory($client, 'hourly_rates', $oldRates, $newRates);

        return back()->with('success', 'Hourly rates saved for ' . $client->name . '.');
    }

    // ── Price history ─────────────────────────────────────────────────
    private function logHistory(Client $client, string $section, array $old, array $new): void
    {
        if (json_encode($old) === json_encode($new)) return;

        ClientPriceHistory::create([
            'client_id'     => $client->id,
            'section'       => $section,
            'previous_data' => $old,
            'new_data'      => $new,
            'changed_by'    => auth()->id(),
        ]);
    }
}
