<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AreaResource;
use App\Models\Area;
use Illuminate\Http\JsonResponse;

class AreaController extends Controller
{
    /** GET /api/areas */
    public function index(): JsonResponse
    {
        $areas = Area::with(['rider', 'parcels'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => AreaResource::collection($areas),
        ]);
    }
}
