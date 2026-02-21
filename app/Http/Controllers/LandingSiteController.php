<?php

namespace App\Http\Controllers;

use App\Models\LandingSite;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LandingSiteController extends Controller
{
    /**
     * Display a listing of resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $sites = LandingSite::query()
            ->when($search, function ($query, $search) {
                $query->where('site_name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('distance', 'like', "%{$search}%")
                    ->orWhere('site_type', 'like', "%{$search}%");
            })
            ->orderBy('site_name')
            ->paginate(10)
            ->withQueryString();
        
        return Inertia::render('LandingSites/Index', [
            'sites' => $sites,
        ]);
    }

    /**
     * Show form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('LandingSites/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'distance' => 'nullable|string|max:50',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'site_type' => 'required|in:dermaga,tangkahan',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        LandingSite::create($validated);

        return redirect()->route('landing-sites.index')
            ->with('success', 'Dermaga berhasil ditambahkan.');
    }

    /**
     * Show form for editing the specified resource.
     */
    public function edit(LandingSite $landingSite)
    {
        return Inertia::render('LandingSites/Edit', [
            'landingSite' => $landingSite,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LandingSite $landingSite)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'distance' => 'nullable|string|max:50',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'site_type' => 'required|in:dermaga,tangkahan',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $landingSite->update($validated);

        return redirect()->route('landing-sites.index')
            ->with('success', 'Dermaga berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LandingSite $landingSite)
    {
        $landingSite->delete();

        return redirect()->route('landing-sites.index')
            ->with('success', 'Dermaga berhasil dihapus.');
    }
}