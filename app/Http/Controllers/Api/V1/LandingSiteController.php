<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LandingSite;
use Illuminate\Http\Request;

class LandingSiteController extends Controller
{
    /**
     * Display a listing of active landing sites.
     */
    public function index()
    {
        $sites = LandingSite::where('is_active', true)
            ->orderBy('site_name')
            ->get(['id', 'site_name', 'site_type']);

        return response()->json([
            'status' => 'success',
            'data' => $sites
        ]);
    }
}
