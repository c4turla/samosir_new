<?php

namespace App\Http\Controllers;

use App\Models\SprDeparture;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SprDepartureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $sprDepartures = SprDeparture::query()
            ->with(['vessel', 'user'])
            ->when($search, function ($query, $search) {
                $query->whereHas('vessel', function ($q) use ($search) {
                    $q->where('vessel_name', 'like', "%{$search}%");
                })->orWhere('nakhoda_name', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('SprDepartures/Index', [
            'sprDepartures' => $sprDepartures,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(SprDeparture $sprDeparture)
    {
        $sprDeparture->load(['vessel', 'user']);

        return Inertia::render('SprDepartures/Show', [
            'sprDeparture' => $sprDeparture
        ]);
    }

    /**
     * Forward the SPR departure request to the syahbandar.
     */
    public function forward(SprDeparture $sprDeparture)
    {
        if (auth()->user()->role !== 'petugas') {
            abort(403, 'Hanya petugas yang dapat memverifikasi dan meneruskan SPR.');
        }

        $sprDeparture->update([
            'status' => 'processed'
        ]);

        $sprDeparture->load('vessel');
        $vesselName = $sprDeparture->vessel ? $sprDeparture->vessel->vessel_name : 'Tidak Diketahui';

        // Notify Syahbandar
        $syahbandars = \App\Models\User::where('role', 'syahbandar')->where('is_active', true)->get();
        foreach ($syahbandars as $syahbandar) {
            $syahbandar->notify(new \App\Notifications\DataInputNotification(
                'Menunggu Approval SPR',
                "Permohonan SPR Keberangkatan Kapal {$vesselName} menunggu approval Anda.",
                '/spr-departures',
                'warning'
            ));
        }

        // Notify the Manager (pengelola) who submitted
        if ($sprDeparture->user) {
            $sprDeparture->user->notify(new \App\Notifications\DataInputNotification(
                'SPR Keberangkatan Diproses',
                "Permohonan SPR Keberangkatan Kapal {$vesselName} telah diproses oleh petugas dan diteruskan ke Syahbandar.",
                '/spr-departures',
                'info'
            ));
        }

        return redirect()->route('spr-departures.show', $sprDeparture->id)
            ->with('success', 'Permohonan SPR berhasil diverifikasi dan diteruskan ke Syahbandar.');
    }

    /**
     * Approve the SPR departure request.
     */
    public function approve(SprDeparture $sprDeparture)
    {
        if (auth()->user()->role !== 'syahbandar') {
            abort(403, 'Hanya Syahbandar yang dapat menyetujui SPR.');
        }

        $sprDeparture->update([
            'status' => 'approved'
        ]);

        $sprDeparture->load('vessel');
        $vesselName = $sprDeparture->vessel ? $sprDeparture->vessel->vessel_name : 'Tidak Diketahui';

        // Notify the Manager (pengelola) who submitted
        if ($sprDeparture->user) {
            $sprDeparture->user->notify(new \App\Notifications\DataInputNotification(
                'SPR Keberangkatan Disetujui',
                "Permohonan SPR Keberangkatan Kapal {$vesselName} telah disetujui oleh Syahbandar.",
                '/spr-departures',
                'success'
            ));
        }

        return redirect()->route('spr-departures.show', $sprDeparture->id)
            ->with('success', 'Permohonan SPR Keberangkatan berhasil disetujui.');
    }

    /**
     * Reject the SPR departure request.
     */
    public function reject(SprDeparture $sprDeparture)
    {
        if (auth()->user()->role !== 'syahbandar') {
            abort(403, 'Hanya Syahbandar yang dapat menolak SPR.');
        }

        $sprDeparture->update([
            'status' => 'rejected'
        ]);

        $sprDeparture->load('vessel');
        $vesselName = $sprDeparture->vessel ? $sprDeparture->vessel->vessel_name : 'Tidak Diketahui';

        // Notify the Manager (pengelola) who submitted
        if ($sprDeparture->user) {
            $sprDeparture->user->notify(new \App\Notifications\DataInputNotification(
                'SPR Keberangkatan Ditolak',
                "Permohonan SPR Keberangkatan Kapal {$vesselName} ditolak oleh Syahbandar.",
                '/spr-departures',
                'danger'
            ));
        }

        return redirect()->route('spr-departures.show', $sprDeparture->id)
            ->with('success', 'Permohonan SPR Keberangkatan ditolak.');
    }
}
