<?php

namespace App\Http\Controllers;

use App\Models\WaterService;
use App\Models\Vessel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class WaterServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = WaterService::with(['vessel'])->latest();

        if ($request->has('search') && $request->search) {
            $query->where('order_number', 'like', '%' . $request->search . '%')
                  ->orWhere('requester', 'like', '%' . $request->search . '%');
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $services = $query->paginate(10)->withQueryString();

        return Inertia::render('WaterServices/Index', [
            'services' => $services,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        $vessels = Vessel::orderBy('vessel_name', 'asc')->get();
        $nextOrderNumber = 'WTR-' . date('Ymd') . '-' . str_pad(WaterService::count() + 1, 4, '0', STR_PAD_LEFT);
        
        return Inertia::render('WaterServices/Create', [
            'vessels' => $vessels,
            'nextOrderNumber' => $nextOrderNumber,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'vessel_id' => 'required|exists:vessels,id',
            'request_date' => 'required|date',
            'requester' => 'nullable|string|max:255',
            'volume' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'field_officer' => 'nullable|string|max:255',
            'treasurer' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $orderNumber = 'WTR-' . date('Ymd') . '-' . str_pad(WaterService::count() + 1, 4, '0', STR_PAD_LEFT);
        $totalPayment = $request->volume * $request->price;

        WaterService::create([
            'vessel_id' => $request->vessel_id,
            'order_number' => $orderNumber,
            'request_date' => $request->request_date,
            'requester' => $request->requester,
            'volume' => $request->volume,
            'price' => $request->price,
            'total_payment' => $totalPayment,
            'field_officer' => $request->field_officer,
            'treasurer' => $request->treasurer,
            'notes' => $request->notes,
            'status' => 'order',
        ]);

        return redirect()->route('water-services.index')->with('success', 'Jasa Air berhasil disimpan.');
    }

    public function show($id)
    {
        $service = WaterService::with(['vessel'])->findOrFail($id);
        return Inertia::render('WaterServices/Show', [
            'service' => $service,
        ]);
    }

    public function edit($id)
    {
        $service = WaterService::findOrFail($id);
        $vessels = Vessel::where('status', 'approved')->get();
        return Inertia::render('WaterServices/Edit', [
            'service' => $service,
            'vessels' => $vessels,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'vessel_id' => 'required|exists:vessels,id',
            'request_date' => 'required|date',
            'requester' => 'nullable|string|max:255',
            'volume' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'field_officer' => 'nullable|string|max:255',
            'treasurer' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:order,processed,completed,cancelled',
        ]);

        $service = WaterService::findOrFail($id);
        $totalPayment = $request->volume * $request->price;

        $service->update([
            'vessel_id' => $request->vessel_id,
            'request_date' => $request->request_date,
            'requester' => $request->requester,
            'volume' => $request->volume,
            'price' => $request->price,
            'total_payment' => $totalPayment,
            'field_officer' => $request->field_officer,
            'treasurer' => $request->treasurer,
            'notes' => $request->notes,
            'status' => $request->status,
        ]);

        return redirect()->route('water-services.index')->with('success', 'Jasa Air berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $service = WaterService::findOrFail($id);
        $service->delete();

        return redirect()->route('water-services.index')->with('success', 'Jasa Air berhasil dihapus.');
    }

    public function complete($id)
    {
        $service = WaterService::findOrFail($id);
        
        $service->update([
            'status' => 'completed'
        ]);

        return redirect()->back()->with('success', 'Orderan berhasil diselesaikan.');
    }

    public function calculation($id)
    {
        $service = WaterService::with(['vessel'])->findOrFail($id);
        
        return Inertia::render('WaterServices/Calculation', [
            'service' => $service
        ]);
    }

    public function calculate(Request $request, $id)
    {
        $request->validate([
            'volume' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'field_officer' => 'nullable|string|max:255',
            'treasurer' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $service = WaterService::findOrFail($id);
        $totalPayment = $request->volume * $request->price;

        $service->update([
            'volume' => $request->volume,
            'price' => $request->price,
            'total_payment' => $totalPayment,
            'field_officer' => $request->field_officer,
            'treasurer' => $request->treasurer,
            'notes' => $request->notes,
            'status' => 'processed',
        ]);

        return redirect()->route('water-services.index')->with('success', 'Perhitungan jasa air berhasil disimpan.');
    }

    public function printOrder($id)
    {
        $service = WaterService::with(['vessel'])->findOrFail($id);
        
        $date = Carbon::parse($service->request_date);
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $hari = $days[$date->dayOfWeek];
        $tanggal = $date->format('d-m-Y');

        $pdf = Pdf::loadView('pdf.water-service-order', compact('service', 'hari', 'tanggal'));
        
        return $pdf->stream('order-air-' . $service->order_number . '.pdf');
    }

    public function printCalculation($id)
    {
        $service = WaterService::with(['vessel'])->findOrFail($id);
        
        if ($service->status === 'order') {
            return redirect()->back()->with('error', 'Perhitungan belum diselesaikan.');
        }

        $date = Carbon::parse($service->request_date);
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $hari = $days[$date->dayOfWeek];
        $tanggal = $date->format('d-m-Y');

        // Reuse order template for now, or create a separate one if needed later
        $pdf = Pdf::loadView('pdf.water-service-order', compact('service', 'hari', 'tanggal'));
        
        return $pdf->stream('perhitungan-air-' . $service->order_number . '.pdf');
    }
}
