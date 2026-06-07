<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;

class SyahbandarController extends Controller
{
    /**
     * Display a listing of active syahbandars.
     */
    public function index()
    {
        $syahbandars = User::where('role', 'syahbandar')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'status' => 'success',
            'data' => $syahbandars
        ]);
    }
}
