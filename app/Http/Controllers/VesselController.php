<?php

namespace App\Http\Controllers;

use App\Models\Vessel;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VesselController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        
        $vessels = Vessel::query()
            ->when($search, function ($query, $search) {
                $query->where('vessel_name', 'like', "%{$search}%")
                    ->orWhere('owner_name', 'like', "%{$search}%")
                    ->orWhere('license_number', 'like', "%{$search}%")
                    ->orWhere('vessel_type', 'like', "%{$search}%");
            })
            ->when($status, function ($query, $status) {
                $query->where('approval_status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();
        
        return Inertia::render('Vessels/Index', [
            'vessels' => $vessels,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Vessels/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vessel_name' => 'required|string|max:100',
            'owner_name' => 'required|string|max:100',
            'license_number' => 'nullable|string|max:100',
            'gt' => 'nullable|string|max:10',
            'fishing_gear' => 'nullable|string|max:100',
            'selar_mark' => 'nullable|string|max:50',
            'vessel_type' => 'nullable|string|max:100',
            'sipi_date' => 'nullable|date',
            'sipi_end_date' => 'nullable|date|after_or_equal:sipi_date',
            'length' => 'nullable|string|max:5',
            'loa' => 'nullable|string|max:5',
            'siup_number' => 'nullable|string|max:50',
            'vessel_photo' => 'nullable|file|image|max:10240', // 10MB max
            'qr_code' => 'nullable|string|max:255',
            'approval_status' => 'required|in:pending,approved,rejected',
            'notes' => 'nullable|string',
        ]);

        // Handle file upload
        if ($request->hasFile('vessel_photo')) {
            $validated['vessel_photo'] = $request->file('vessel_photo')->store('vessel-photos', 'public');
        }

        $validated['registered_by'] = auth()->id();
        $validated['approval_status'] = 'approved';

        Vessel::create($validated);

        return redirect()->route('vessels.index')
            ->with('success', 'Kapal berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vessel $vessel)
    {
        return Inertia::render('Vessels/Edit', [
            'vessel' => $vessel->append('vessel_photo_url'),
        ]);
    }

    /**
     * Update specified resource in storage.
     */
    public function update(Request $request, Vessel $vessel)
    {
        $validated = $request->validate([
            'vessel_name' => 'required|string|max:100',
            'owner_name' => 'required|string|max:100',
            'license_number' => 'nullable|string|max:100',
            'gt' => 'nullable|string|max:10',
            'fishing_gear' => 'nullable|string|max:100',
            'selar_mark' => 'nullable|string|max:50',
            'vessel_type' => 'nullable|string|max:100',
            'sipi_date' => 'nullable|date',
            'sipi_end_date' => 'nullable|date|after_or_equal:sipi_date',
            'length' => 'nullable|string|max:5',
            'loa' => 'nullable|string|max:5',
            'siup_number' => 'nullable|string|max:50',
            'vessel_photo' => 'nullable|file|image|max:10240', // 10MB max
            'qr_code' => 'nullable|string|max:255',
            'approval_status' => 'required|in:pending,approved,rejected',
            'notes' => 'nullable|string',
        ]);

        // Handle file upload
        if ($request->hasFile('vessel_photo')) {
            $validated['vessel_photo'] = $request->file('vessel_photo')->store('vessel-photos', 'public');
        }

        // Handle approval status changes
        if ($vessel->approval_status !== $request->approval_status) {
            if ($request->approval_status === 'approved') {
                $validated['approved_by'] = auth()->id();
                $validated['approved_at'] = now();
            } else {
                $validated['approved_by'] = null;
                $validated['approved_at'] = null;
            }
        }

        $vessel->update($validated);

        return redirect()->route('vessels.index')
            ->with('success', 'Data kapal berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vessel $vessel)
    {
        $vessel->delete();

        return redirect()->route('vessels.index')
            ->with('success', 'Kapal berhasil dihapus.');
    }

    /**
     * Approve a vessel.
     */
    public function approve(Vessel $vessel)
    {
        $vessel->update([
            'approval_status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('vessels.index')
            ->with('success', 'Kapal berhasil disetujui.');
    }

    /**
     * Reject a vessel.
     */
    public function reject(Vessel $vessel)
    {
        $vessel->update([
            'approval_status' => 'rejected',
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return redirect()->route('vessels.index')
            ->with('success', 'Kapal berhasil ditolak.');
    }

    /**
     * Show approval page with manager assignment.
     */
    public function approval(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        
        $vessels = Vessel::query()
            ->when($search, function ($query, $search) {
                $query->where('vessel_name', 'like', "%{$search}%")
                    ->orWhere('owner_name', 'like', "%{$search}%")
                    ->orWhere('license_number', 'like', "%{$search}%")
                    ->orWhere('vessel_type', 'like', "%{$search}%");
            })
            ->when($status, function ($query, $status) {
                $query->where('approval_status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();
        
        // Load managers for each vessel
        $vessels->getCollection()->each(function ($vessel) {
            $vessel->load('managers');
        });

        $users = User::select('id', 'name', 'email')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Vessels/Approval', [
            'vessels' => $vessels,
            'users' => $users,
        ]);
    }

    /**
     * Assign manager to vessel.
     */
    public function assignManager(Request $request, Vessel $vessel)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'is_primary' => 'nullable|boolean',
            'address' => 'nullable|string',
            'id_card' => 'nullable|string|max:255',
            'authorization_letter' => 'nullable|string|max:255',
        ]);

        // Check if manager already exists
        $existingManager = $vessel->managers()
            ->where('user_id', $validated['user_id'])
            ->first();

        if ($existingManager) {
            return back()->with('error', 'Pengelola ini sudah terdaftar untuk kapal ini.');
        }

        // If setting as primary, remove primary from other managers
        if ($request->has('is_primary') && $validated['is_primary']) {
            $vessel->managers()->update(['is_primary' => false]);
        }

        $vessel->managers()->attach($validated['user_id'], [
            'is_primary' => $validated['is_primary'] ?? false,
            'address' => $validated['address'] ?? null,
            'id_card' => $validated['id_card'] ?? null,
            'authorization_letter' => $validated['authorization_letter'] ?? null,
        ]);

        return back()->with('success', 'Pengelola kapal berhasil ditambahkan.');
    }

    /**
     * Remove manager from vessel.
     */
    public function removeManager(Vessel $vessel, User $user)
    {
        $vessel->managers()->detach($user->id);

        return back()->with('success', 'Pengelola kapal berhasil dihapus.');
    }

    /**
     * Update manager details.
     */
    public function updateManager(Request $request, Vessel $vessel, User $user)
    {
        $validated = $request->validate([
            'is_primary' => 'nullable|boolean',
            'address' => 'nullable|string',
            'id_card' => 'nullable|string|max:255',
            'authorization_letter' => 'nullable|string|max:255',
        ]);

        // If setting as primary, remove primary from other managers
        if ($request->has('is_primary') && $validated['is_primary']) {
            $vessel->managers()->update(['is_primary' => false]);
        }

        $vessel->managers()->updateExistingPivot($user->id, [
            'is_primary' => $validated['is_primary'] ?? false,
            'address' => $validated['address'] ?? null,
            'id_card' => $validated['id_card'] ?? null,
            'authorization_letter' => $validated['authorization_letter'] ?? null,
        ]);

        return back()->with('success', 'Data pengelola kapal berhasil diperbarui.');
    }
}
