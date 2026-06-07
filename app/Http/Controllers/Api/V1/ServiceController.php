<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\WaterService;
use App\Models\EquipmentService;
use App\Models\EquipmentItem;
use App\Models\User;
use App\Notifications\DataInputNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    /**
     * Display a listing of services (water and equipment/ice cruiser).
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $waterQuery = WaterService::with(['vessel']);
        $eqQuery = EquipmentService::with(['vessel', 'items']);
        
        if ($user->role === 'pengelola') {
            $vesselIds = $user->vessels()->pluck('vessels.id');
            $waterQuery->whereIn('vessel_id', $vesselIds);
            $eqQuery->whereIn('vessel_id', $vesselIds);
        }
        
        $waterServices = $waterQuery->latest('request_date')->take(20)->get();
        $eqServices = $eqQuery->latest('service_date')->take(20)->get();
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'water' => $waterServices,
                'equipment' => $eqServices,
            ]
        ]);
    }

    /**
     * Store a new Water Service order.
     */
    public function storeWater(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vessel_id' => 'required|exists:vessels,id',
            'request_date' => 'required|date',
            'requester' => 'nullable|string|max:255',
            'volume' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'field_officer' => 'nullable|string|max:255',
            'treasurer' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        $orderNumber = 'WTR-' . date('Ymd') . '-' . str_pad(WaterService::count() + 1, 4, '0', STR_PAD_LEFT);
        $totalPayment = $request->volume * $request->price;

        $service = WaterService::create([
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

        // Notify
        $service->load('vessel');
        $vesselName = $service->vessel ? $service->vessel->vessel_name : 'Tidak Diketahui';
        $orderNumber = $service->order_number;

        // 1. Notify Petugas
        $petugasUsers = User::where('role', 'petugas')->where('is_active', true)->get();
        foreach ($petugasUsers as $petugas) {
            $petugas->notify(new DataInputNotification(
                'Menunggu Approval Jasa',
                "Pemesanan Jasa Air Tawar untuk kapal {$vesselName} ({$orderNumber}) menunggu approval Anda.",
                '/water-services',
                'warning'
            ));
        }

        // 2. Notify Requesting User
        $request->user()->notify(new DataInputNotification(
            'Pemesanan Jasa Berhasil',
            "Pemesanan Jasa Air Tawar untuk kapal {$vesselName} ({$orderNumber}) berhasil diajukan.",
            '/services',
            'success'
        ));

        return response()->json([
            'status' => 'success',
            'message' => 'Jasa Air berhasil dipesan.',
            'data' => $service
        ], 201);
    }

    /**
     * Store a new Equipment Service order.
     */
    public function storeEquipment(Request $request)
    {
        $validator = Validator::make($request->all(), [
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

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

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

        $createdItems = [];
        foreach ($request->items as $item) {
            $createdItems[] = EquipmentItem::create([
                'equipment_service_id' => $service->id,
                'equipment_name' => $item['equipment_name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $item['quantity'] * $item['unit_price'],
                'notes' => $item['notes'] ?? null,
            ]);
        }

        $service->load('items');

        // Notify
        $service->load('vessel');
        $vesselName = $service->vessel ? $service->vessel->vessel_name : 'Tidak Diketahui';
        $orderNumber = $service->order_number;

        // 1. Notify Petugas
        $petugasUsers = User::where('role', 'petugas')->where('is_active', true)->get();
        foreach ($petugasUsers as $petugas) {
            $petugas->notify(new DataInputNotification(
                'Menunggu Approval Jasa',
                "Pemesanan Jasa Peralatan untuk kapal {$vesselName} ({$orderNumber}) menunggu approval Anda.",
                '/equipment-services',
                'warning'
            ));
        }

        // 2. Notify Requesting User
        $request->user()->notify(new DataInputNotification(
            'Pemesanan Jasa Berhasil',
            "Pemesanan Jasa Peralatan untuk kapal {$vesselName} ({$orderNumber}) berhasil diajukan.",
            '/services',
            'success'
        ));

        return response()->json([
            'status' => 'success',
            'message' => 'Jasa Peralatan berhasil dipesan.',
            'data' => $service
        ], 201);
    }

    /**
     * Store a new Ice Cruiser Service order.
     */
    public function storeIceCruiser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'renter_name' => 'required|string|max:255',
            'service_date' => 'required|date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'field_officer' => 'nullable|string|max:255',
            'treasurer' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.equipment_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'quantity' => 'required_without:items|nullable|integer|min:1',
            'unit_price' => 'required_without:items|nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        $orderNumber = 'ICE-' . date('Ymd') . '-' . str_pad(EquipmentService::count() + 1, 4, '0', STR_PAD_LEFT);

        $items = $request->items ?? [];
        if (empty($items) && $request->has('quantity')) {
            $items[] = [
                'equipment_name' => 'ice_cruiser',
                'quantity' => $request->quantity,
                'unit_price' => $request->unit_price,
                'notes' => $request->notes,
            ];
        }

        $totalAmount = 0;
        foreach ($items as $item) {
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

        foreach ($items as $item) {
            EquipmentItem::create([
                'equipment_service_id' => $service->id,
                'equipment_name' => $item['equipment_name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $item['quantity'] * $item['unit_price'],
                'notes' => $item['notes'] ?? null,
            ]);
        }

        $service->load('items');

        // Notify
        $service->load('vessel');
        $vesselName = $service->vessel ? $service->vessel->vessel_name : 'Tidak Diketahui';
        $orderNumber = $service->order_number;

        // 1. Notify Petugas
        $petugasUsers = User::where('role', 'petugas')->where('is_active', true)->get();
        foreach ($petugasUsers as $petugas) {
            $petugas->notify(new DataInputNotification(
                'Menunggu Approval Jasa',
                "Pemesanan Jasa Ice Cruiser untuk kapal {$vesselName} ({$orderNumber}) menunggu approval Anda.",
                '/equipment-services',
                'warning'
            ));
        }

        // 2. Notify Requesting User
        $request->user()->notify(new DataInputNotification(
            'Pemesanan Jasa Berhasil',
            "Pemesanan Jasa Ice Cruiser untuk kapal {$vesselName} ({$orderNumber}) berhasil diajukan.",
            '/services',
            'success'
        ));

        return response()->json([
            'status' => 'success',
            'message' => 'Jasa Ice Cruiser berhasil dipesan.',
            'data' => $service
        ], 201);
    }
}
