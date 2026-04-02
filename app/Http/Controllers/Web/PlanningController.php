<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DayDemand;
use App\Models\Client;
use App\Models\Product;
use Illuminate\Http\Request;

class PlanningController extends Controller
{
    public function index(Request $request)
    {
        // Show all non-cancelled demands from today onwards, grouped by date
        // Allocated ones are shown but faded — useful for reference
        $demands = DayDemand::where('date', '>=', today()->toDateString())
            ->where('status', '!=', 'cancelled')
            ->with([
                'client',
                'site',
                'product',
                'books.jobs',
                'books.workers',
            ])
            ->orderBy('date')
            ->orderByRaw("CASE status WHEN 'pending' THEN 1 WHEN 'partial' THEN 2 WHEN 'allocated' THEN 3 END")
            ->get();

        $clients     = Client::where('is_active', true)->with('activeSites')->orderBy('name')->get();
        $products    = Product::where('is_active', true)->orderBy('name')->get();

        // Default date for the add form — use tomorrow if today already has demands, else today
        $defaultDate = $request->get('date', today()->addDay()->toDateString());

        return view('planning.index', compact('demands', 'clients', 'products', 'defaultDate'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'        => 'required|date',
            'client_id'   => 'required|exists:clients,id',
            'site_id'     => 'required|exists:sites,id',
            'product_id'  => 'nullable|exists:products,id',
            'qty_40ft'    => 'integer|min:0',
            'qty_20ft'    => 'integer|min:0',
            'qty_workers' => 'integer|min:0',
        ]);

        DayDemand::create([
            'date'        => $request->date,
            'client_id'   => $request->client_id,
            'site_id'     => $request->site_id,
            'product_id'  => $request->product_id ?: null,
            'qty_40ft'    => $request->qty_40ft ?? 0,
            'qty_20ft'    => $request->qty_20ft ?? 0,
            'qty_workers' => $request->qty_workers ?? 0,
            'notes'       => $request->notes,
            'created_by'  => auth()->id(),
        ]);

        return redirect()
            ->route('planning.index')
            ->with('success', 'Demand added for ' . \Carbon\Carbon::parse($request->date)->format('d M Y') . '.');
    }

    public function markAllocated(DayDemand $demand)
    {
        $demand->markAllocated();
        return back()->with('success', 'Demand marked as fully allocated.');
    }

    public function cancel(DayDemand $demand)
    {
        $demand->markCancelled();
        return back()->with('success', 'Demand cancelled.');
    }

    public function destroy(DayDemand $demand)
    {
        $demand->delete();
        return back()->with('success', 'Demand deleted.');
    }
}
