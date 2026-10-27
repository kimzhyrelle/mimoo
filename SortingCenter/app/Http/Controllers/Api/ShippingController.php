<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShippingZone;
use App\Services\ShippingQuoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function quote(Request $request, ShippingQuoteService $quotes): JsonResponse
    {
        $data = $request->validate([
            'address' => 'required|string|max:255',
            'region' => 'nullable|string|max:120',
            'weight_kg' => 'required|numeric|min:0.01|max:1000',
        ]);

        $quote = $quotes->quote($data['address'], (float) $data['weight_kg'], $data['region'] ?? null);

        return response()->json([
            'ok' => true,
            'data' => $quote,
            'zones' => ShippingZone::where('is_serviceable', true)->orderBy('name')->pluck('name'),
        ]);
    }
}
