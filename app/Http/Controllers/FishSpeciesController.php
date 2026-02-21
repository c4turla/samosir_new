<?php

namespace App\Http\Controllers;

use App\Models\FishSpecies;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FishSpeciesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $species = FishSpecies::query()
            ->when($search, function ($query, $search) {
                $query->where('species_name', 'like', "%{$search}%")
                    ->orWhere('local_name', 'like', "%{$search}%")
                    ->orWhere('scientific_name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            })
            ->orderBy('species_name')
            ->paginate(10)
            ->withQueryString();
        
        return Inertia::render('FishSpecies/Index', [
            'species' => $species,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('FishSpecies/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'species_name' => 'required|string|max:255',
            'local_name' => 'nullable|string|max:255',
            'scientific_name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        FishSpecies::create($validated);

        return redirect()->route('fish-species.index')
            ->with('success', 'Jenis ikan berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FishSpecies $fishSpecies)
    {
        return Inertia::render('FishSpecies/Edit', [
            'fishSpecies' => $fishSpecies,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FishSpecies $fishSpecies)
    {
        $validated = $request->validate([
            'species_name' => 'required|string|max:255',
            'local_name' => 'nullable|string|max:255',
            'scientific_name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $fishSpecies->update($validated);

        return redirect()->route('fish-species.index')
            ->with('success', 'Jenis ikan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FishSpecies $fishSpecies)
    {
        $fishSpecies->delete();

        return redirect()->route('fish-species.index')
            ->with('success', 'Jenis ikan berhasil dihapus.');
    }
}