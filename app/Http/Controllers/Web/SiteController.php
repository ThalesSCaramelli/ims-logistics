<?php
// ── SiteController ────────────────────────────────────────────────────
// Place in app/Http/Controllers/Web/SiteController.php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name'      => 'required|string|max:100',
            'address'   => 'nullable|string|max:200',
        ]);

        Site::create([
            'client_id' => $request->client_id,
            'name'      => $request->name,
            'address'   => $request->address,
            'is_active' => true,
        ]);

        return back()->with('success', 'Site added.');
    }

    public function toggleActive(Site $site)
    {
        $site->update(['is_active' => !$site->is_active]);
        return back()->with('success', 'Site ' . ($site->is_active ? 'activated' : 'deactivated') . '.');
    }
}
