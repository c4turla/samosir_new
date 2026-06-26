<?php

namespace App\Http\Controllers;

use App\Models\Unloading;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ApprovalController extends Controller
{

    public function approve(Unloading $unloading)
    {
        $user = auth()->user();
        
        // Only syahbandar can approve
        if ($user->role !== 'syahbandar') {
            abort(403, 'Hanya syahbandar yang dapat melakukan approval');
        }
        
        // Only syahbandar assigned to this unloading can approve it
        if ($unloading->syahbandar_id !== $user->id) {
            abort(403, 'Anda tidak memiliki izin untuk melakukan approval pada data ini');
        }
        
        $unloading->update([
            'approval_status' => true,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Data penimbangan ikan berhasil disetujui');
    }

    public function reject(Unloading $unloading)
    {
        $user = auth()->user();
        
        // Only syahbandar can reject
        if ($user->role !== 'syahbandar') {
            abort(403, 'Hanya syahbandar yang dapat menolak approval');
        }
        
        // Only syahbandar assigned to this unloading can reject it
        if ($unloading->syahbandar_id !== $user->id) {
            abort(403, 'Anda tidak memiliki izin untuk menolak data ini');
        }
        
        $unloading->update(['approval_status' => false]);

        return redirect()->back()->with('success', 'Data penimbangan ikan berhasil ditolak');
    }
}
