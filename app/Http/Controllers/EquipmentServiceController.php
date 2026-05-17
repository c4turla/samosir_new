<?php

namespace App\Http\Controllers;

use App\Models\EquipmentService;
use App\Models\EquipmentItem;
use App\Models\Vessel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class EquipmentServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = EquipmentService::with(['vessel', 'items'])->latest();

        if ($request->has('search') && $request->search) {
            $query->where('order_number', 'like', '%' . $request->search . '%')
                ->orWhere('renter_name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $services = $query->paginate(10)->withQueryString();

        return Inertia::render('EquipmentServices/Index', [
            'services' => $services,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        $vessels = Vessel::orderBy('vessel_name', 'asc')->get();
        return Inertia::render('EquipmentServices/Create', [
            'vessels' => $vessels,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'vessel_id' => 'required|exists:vessels,id',
            'renter_name' => 'required|string|max:255',
            'service_date' => 'required|date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'field_officer' => 'nullable|string|max:255',
            'treasurer' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.equipment_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $orderNumber = 'EQS-' . date('Ymd') . '-' . str_pad(EquipmentService::count() + 1, 4, '0', STR_PAD_LEFT);

        $totalAmount = 0;
        foreach ($request->items as $item) {
            $totalAmount += $item['quantity'] * $item['unit_price'];
        }

        $service = EquipmentService::create([
            'vessel_id' => $request->vessel_id,
            'order_number' => $orderNumber,
            'renter_name' => $request->renter_name,
            'service_date' => $request->service_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'officer' => $request->field_officer,
            'treasurer' => $request->treasurer,
            'total_amount' => $totalAmount,
            'notes' => $request->notes,
            'status' => 'order',
        ]);

        foreach ($request->items as $item) {
            EquipmentItem::create([
                'equipment_service_id' => $service->id,
                'equipment_name' => $item['equipment_name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $item['quantity'] * $item['unit_price'],
                'notes' => $item['notes'] ?? null,
            ]);
        }

        return redirect()->route('equipment-services.index')->with('success', 'Jasa peralatan berhasil disimpan.');
    }

    public function show($id)
    {
        $service = EquipmentService::with(['vessel', 'items'])->findOrFail($id);
        return Inertia::render('EquipmentServices/Show', [
            'service' => $service,
        ]);
    }

    public function edit($id)
    {
        $service = EquipmentService::with(['vessel', 'items'])->findOrFail($id);
        $vessels = Vessel::orderBy('vessel_name', 'asc')->get();
        return Inertia::render('EquipmentServices/Edit', [
            'service' => $service,
            'vessels' => $vessels,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'vessel_id' => 'required|exists:vessels,id',
            'renter_name' => 'required|string|max:255',
            'service_date' => 'required|date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'field_officer' => 'nullable|string|max:255',
            'treasurer' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:order,processed,completed,cancelled',
            'items' => 'required|array|min:1',
            'items.*.equipment_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $service = EquipmentService::findOrFail($id);

        $totalAmount = 0;
        foreach ($request->items as $item) {
            $totalAmount += $item['quantity'] * $item['unit_price'];
        }

        $service->update([
            'vessel_id' => $request->vessel_id,
            'renter_name' => $request->renter_name,
            'service_date' => $request->service_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'officer' => $request->field_officer,
            'treasurer' => $request->treasurer,
            'total_amount' => $totalAmount,
            'notes' => $request->notes,
            'status' => $request->status,
        ]);

        // Delete old items and create new ones
        $service->items()->delete();
        foreach ($request->items as $item) {
            EquipmentItem::create([
                'equipment_service_id' => $service->id,
                'equipment_name' => $item['equipment_name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $item['quantity'] * $item['unit_price'],
                'notes' => $item['notes'] ?? null,
            ]);
        }

        return redirect()->route('equipment-services.index')->with('success', 'Jasa peralatan berhasil diperbarui.');
    }

    public function printOrder($id)
    {
        $service = EquipmentService::with(['vessel', 'items'])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.equipment-service-order', compact('service'));

        return $pdf->download('order-peralatan-' . $service->order_number . '.pdf');
    }

    public function calculation($id)
    {
        $service = EquipmentService::with(['vessel', 'items'])->findOrFail($id);

        return Inertia::render('EquipmentServices/Calculation', [
            'service' => $service
        ]);
    }

    public function printCalculation($id)
    {
        $service = EquipmentService::with(['vessel', 'items'])->findOrFail($id);

        if ($service->status !== 'processed') {
            return redirect()->back()->with('error', 'Perhitungan belum diselesaikan.');
        }

        $terbilang = $this->terbilang($service->total_amount) . ' rupiah';

        $pdf = Pdf::loadView('pdf.equipment-service-calculation', compact('service', 'terbilang'));

        return $pdf->download('perhitungan-peralatan-' . $service->order_number . '.pdf');
    }

    private function terbilang($nilai)
    {
        $nilai = abs($nilai);
        $huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
        $temp = "";
        if ($nilai < 12) {
            $temp = " " . $huruf[$nilai];
        } else if ($nilai < 20) {
            $temp = $this->terbilang($nilai - 10) . " belas";
        } else if ($nilai < 100) {
            $temp = $this->terbilang($nilai / 10) . " puluh" . $this->terbilang($nilai % 10);
        } else if ($nilai < 200) {
            $temp = " seratus" . $this->terbilang($nilai - 100);
        } else if ($nilai < 1000) {
            $temp = $this->terbilang($nilai / 100) . " ratus" . $this->terbilang($nilai % 100);
        } else if ($nilai < 2000) {
            $temp = " seribu" . $this->terbilang($nilai - 1000);
        } else if ($nilai < 1000000) {
            $temp = $this->terbilang($nilai / 1000) . " ribu" . $this->terbilang($nilai % 1000);
        } else if ($nilai < 1000000000) {
            $temp = $this->terbilang($nilai / 1000000) . " juta" . $this->terbilang($nilai % 1000000);
        } else if ($nilai < 1000000000000) {
            $temp = $this->terbilang($nilai / 1000000000) . " milyar" . $this->terbilang(fmod($nilai, 1000000000));
        } else if ($nilai < 1000000000000000) {
            $temp = $this->terbilang($nilai / 1000000000000) . " trilyun" . $this->terbilang(fmod($nilai, 1000000000000));
        }
        return trim($temp);
    }

    public function calculate(Request $request, $id)
    {
        $request->validate([
            'start_time' => 'required',
            'end_time' => 'required',
            'duration' => 'required|numeric',
            'items' => 'required|array',
            'items.*.id' => 'required|exists:equipment_items,id',
            'items.*.quantity' => 'required|integer|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.subtotal' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'officer' => 'nullable|string|max:255',
            'treasurer' => 'nullable|string|max:255',
        ]);

        $service = EquipmentService::findOrFail($id);

        $service->update([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'duration' => $request->duration,
            'officer' => $request->officer,
            'treasurer' => $request->treasurer,
            'total_amount' => $request->total_amount,
            'status' => 'processed'
        ]);

        foreach ($request->items as $itemData) {
            $item = EquipmentItem::findOrFail($itemData['id']);
            $item->update([
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'subtotal' => $itemData['subtotal'],
            ]);
        }

        return redirect()->route('equipment-services.index')->with('success', 'Perhitungan biaya berhasil disimpan.');
    }

    public function destroy($id)
    {
        $service = EquipmentService::findOrFail($id);
        $service->items()->delete();
        $service->delete();

        return redirect()->route('equipment-services.index')->with('success', 'Jasa peralatan berhasil dihapus.');
    }

    public function complete($id)
    {
        $service = EquipmentService::findOrFail($id);

        if ($service->status !== 'processed') {
            return redirect()->back()->with('error', 'Pesanan harus dalam status diproses terlebih dahulu.');
        }

        $service->update(['status' => 'completed']);

        return redirect()->back()->with('success', 'Pembayaran berhasil diselesaikan.');
    }
}
