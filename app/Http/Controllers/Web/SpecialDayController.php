<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SpecialDay;
use Illuminate\Http\Request;

class SpecialDayController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'date'        => 'required|date|unique:special_days,date',
            'description' => 'required|string|max:100',
        ]);

        SpecialDay::create([
            'date'        => $request->date,
            'description' => $request->description,
            'is_active'   => true,
        ]);

        return back()->with('success', 'Special day added.');
    }

    public function destroy(SpecialDay $specialDay)
    {
        $specialDay->delete();
        return back()->with('success', 'Special day removed.');
    }
}
