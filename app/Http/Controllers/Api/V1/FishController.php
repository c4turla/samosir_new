<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FishSpecies;
use Illuminate\Http\Request;

class FishController extends Controller
{
    /**
     * Display a listing of the fish species.
     */
    public function index()
    {
        $fish = FishSpecies::where('is_active', true)->get();

        return response()->json([
            'status' => 'success',
            'data' => $fish
        ]);
    }
}
