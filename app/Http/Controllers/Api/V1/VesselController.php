<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Vessel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class VesselController extends Controller
{
    /**
     * Get available vessels (not yet managed by anyone or only have rejected status)
     */
    public function available(Request $request)
    {
        $search = $request->query('search');
        $perPage = $request->query('per_page', 15);

        $vessels = Vessel::whereDoesntHave('managers', function ($query) {
            $query->whereIn('status', ['pending', 'approved']);
        })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('vessel_name', 'like', "%{$search}%")
                      ->orWhere('owner_name', 'like', "%{$search}%")
                      ->orWhere('license_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('vessel_name')
            ->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $vessels
        ]);
    }

    /**
     * Register current user as manager for a vessel
     */
    public function registerManager(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vessel_id' => 'required|exists:vessels,id',
            'address' => 'nullable|string|max:500',
            'id_card' => 'nullable|string|max:255',
            'authorization_letter' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $vessel = Vessel::findOrFail($request->vessel_id);
        $user = auth()->user();

        // Check if user is already a manager for this vessel with pending or approved status
        $existingManager = $vessel->managers()
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingManager) {
            if ($existingManager->pivot->status === 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pendaftaran Anda masih menunggu persetujuan.'
                ], 400);
            }
            return response()->json([
                'status' => 'error',
                'message' => 'Anda sudah terdaftar sebagai pengelola untuk kapal ini.'
            ], 400);
        }

        // Check if user has pengelola role
        if ($user->role !== 'pengelola') {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya user dengan role pengelola yang dapat mendaftarkan diri.'
            ], 403);
        }

        // Check if vessel has any approved managers
        $hasApprovedManager = $vessel->managers()->where('status', 'approved')->exists();

        // Attach with pending status
        $vessel->managers()->attach($user->id, [
            'address' => $request->address,
            'id_card' => $request->id_card ?? $user->id_card,
            'authorization_letter' => $request->authorization_letter ?? $user->authorization_letter,
            'status' => 'pending', // Always pending until approved by officers
            'is_primary' => !$vessel->managers()->wherePivot('is_primary', true)->exists(),
            'approved_by' => null,
            'approved_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $message = 'Pendaftaran pengelola kapal berhasil diajukan. Mohon tunggu persetujuan petugas.';

        return response()->json([
            'status' => 'success',
            'message' => $message
        ]);
    }

    /**
     * Get vessels managed by current user
     */
    public function myVessels(Request $request)
    {
        $user = auth()->user();
        $status = $request->query('status'); // pending, approved, rejected, all
        $perPage = $request->query('per_page', 15);

        $query = Vessel::whereHas('managers', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->whereIn('status', ['pending', 'approved']);
        })
        ->with(['managers' => function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->select('users.id', 'users.name', 'users.email', 'users.phone')
                  ->withPivot('is_primary', 'address', 'id_card', 'authorization_letter', 'status');
        }]);

        if ($status && $status !== 'all') {
            $query->whereHas('managers', function ($query) use ($user, $status) {
                $query->where('user_id', $user->id)
                      ->where('status', $status);
            });
        }

        $vessels = $query->orderBy('vessel_name')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $vessels
        ]);
    }

    /**
     * Get single vessel details with manager info
     */
    public function show($id)
    {
        $user = auth()->user();

        $vessel = Vessel::with(['managers' => function ($query) {
            $query->select('users.id', 'users.name', 'users.email', 'users.phone')
                  ->withPivot('is_primary', 'address', 'id_card', 'authorization_letter', 'status', 'approved_by', 'approved_at');
        }])
        ->with('registeredBy:id,name')
        ->find($id);

        if (!$vessel) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kapal tidak ditemukan.'
            ], 404);
        }

        // Check if user is a manager of this vessel
        $isManager = $vessel->managers()
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        // Add is_manager flag to response
        $vessel->is_manager = $isManager;
        $vessel->user_manager_status = $isManager
            ? $vessel->managers->where('id', $user->id)->first()?->pivot?->status
            : null;

        return response()->json([
            'status' => 'success',
            'data' => $vessel
        ]);
    }

    /**
     * Update manager info
     */
    public function updateManager(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vessel_id' => 'required|exists:vessels,id',
            'address' => 'nullable|string|max:500',
            'id_card' => 'nullable|string|max:255',
            'authorization_letter' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $vessel = Vessel::findOrFail($request->vessel_id);

        $manager = $vessel->managers()
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if (!$manager) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda bukan pengelola kapal ini.'
            ], 403);
        }

        // Update manager info
        $vessel->managers()->updateExistingPivot($user->id, [
            'address' => $request->address ?? $manager->pivot->address,
            'id_card' => $request->id_card ?? $manager->pivot->id_card,
            'authorization_letter' => $request->authorization_letter ?? $manager->pivot->authorization_letter,
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data pengelola kapal berhasil diperbarui.'
        ]);
    }

    /**
     * Unregister from managing a vessel
     */
    public function unregisterManager(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vessel_id' => 'required|exists:vessels,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $vessel = Vessel::findOrFail($request->vessel_id);

        $manager = $vessel->managers()
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if (!$manager) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda bukan pengelola kapal ini.'
            ], 403);
        }

        // Soft delete the manager relationship
        DB::table('vessel_managers')
            ->where('user_id', $user->id)
            ->where('vessel_id', $request->vessel_id)
            ->update(['deleted_at' => now()]);

        // If this was the primary manager, assign primary to another approved manager
        if ($manager->pivot->is_primary) {
            $nextManager = $vessel->managers()
                ->where('status', 'approved')
                ->where('user_id', '!=', $user->id)
                ->first();

            if ($nextManager) {
                $vessel->managers()->updateExistingPivot($nextManager->id, [
                    'is_primary' => true,
                    'updated_at' => now(),
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil membatalkan pengelolaan kapal.'
        ]);
    }

    /**
     * Get all managers of a specific vessel (for vessel owner/admin)
     */
    public function vesselManagers($vesselId)
    {
        $vessel = Vessel::with(['managers' => function ($query) {
            $query->select('users.id', 'users.name', 'users.email', 'users.phone', 'users.role')
                  ->withPivot('is_primary', 'address', 'id_card', 'authorization_letter', 'status', 'approved_by', 'approved_at');
        }])->find($vesselId);

        if (!$vessel) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kapal tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $vessel->managers
        ]);
    }

    /**
     * Get all vessels managed by current user (summary)
     */
    public function summary()
    {
        $user = auth()->user();

        $stats = [
            'total' => Vessel::whereHas('managers', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->whereIn('status', ['pending', 'approved']);
            })->count(),
            'pending' => Vessel::whereHas('managers', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->where('status', 'pending');
            })->count(),
            'approved' => Vessel::whereHas('managers', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->where('status', 'approved');
            })->count(),
            'as_primary' => Vessel::whereHas('managers', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->where('status', 'approved')
                      ->where('is_primary', true);
            })->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }
}
