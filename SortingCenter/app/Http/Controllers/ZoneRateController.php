<?php

namespace App\Http\Controllers;

use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Services\AuditLogger;
use App\Support\CompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ZoneRateController extends Controller
{
    public function index(): View
    {
        return view('sorting.zones.index', [
            'zones' => ShippingZone::with(['rates' => fn ($q) => $q->orderBy('min_weight_kg')])->orderBy('name')->get(),
        ]);
    }

    public function storeZone(Request $request, AuditLogger $audit): JsonResponse|RedirectResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|min:2|max:80',
                'region' => 'nullable|string|max:120',
                'areas' => 'nullable|string|max:2000',
                'is_serviceable' => 'nullable|boolean',
            ]);
            $zone = ShippingZone::create([
                'company_id' => CompanyContext::id(),
                'name' => $data['name'],
                'region' => $data['region'] ?? null,
                'areas' => $this->splitAreas($data['areas'] ?? ''),
                'is_serviceable' => (bool) ($data['is_serviceable'] ?? true),
            ]);
        } catch (ValidationException $e) {
            return $this->error($request, $e);
        }

        $audit->log('zone.created', $zone, [], ['name' => $zone->name], 'Shipping zone created');

        return $this->ok($request, 'Shipping zone created.', $zone);
    }

    public function updateZone(Request $request, ShippingZone $zone, AuditLogger $audit): JsonResponse|RedirectResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|min:2|max:80',
                'region' => 'nullable|string|max:120',
                'areas' => 'nullable|string|max:2000',
                'is_serviceable' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return $this->error($request, $e);
        }

        $old = $zone->only(['name', 'region', 'is_serviceable']);
        $zone->update([
            'name' => $data['name'],
            'region' => $data['region'] ?? null,
            'areas' => $this->splitAreas($data['areas'] ?? ''),
            'is_serviceable' => (bool) ($data['is_serviceable'] ?? false),
        ]);

        $audit->log('zone.updated', $zone, $old, $zone->only(['name', 'region', 'is_serviceable']), 'Shipping zone updated');

        return $this->ok($request, 'Shipping zone updated.', $zone->fresh());
    }

    public function storeRate(Request $request, ShippingZone $zone, AuditLogger $audit): JsonResponse|RedirectResponse
    {
        $data = $this->validateRate($request);
        $rate = $zone->rates()->create($data + ['company_id' => $zone->company_id, 'is_active' => true]);
        $audit->log('rate.created', $rate, [], $rate->only(['shipping_zone_id', 'base_fee', 'per_kg_fee']), 'Shipping rate created');

        return $this->ok($request, 'Shipping rate added.', $rate);
    }

    public function updateRate(Request $request, ShippingZone $zone, ShippingRate $rate, AuditLogger $audit): JsonResponse|RedirectResponse
    {
        if ($rate->shipping_zone_id !== $zone->id) {
            abort(404);
        }

        $data = $this->validateRate($request);
        $old = $rate->only(['base_fee', 'per_kg_fee', 'min_weight_kg', 'max_weight_kg']);
        $rate->update($data);
        $audit->log('rate.updated', $rate, $old, $rate->only(['base_fee', 'per_kg_fee', 'min_weight_kg', 'max_weight_kg']), 'Shipping rate updated');

        return $this->ok($request, 'Shipping rate updated.', $rate->fresh(), route('zones.index'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validateRate(Request $request): array
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:80',
            'base_fee' => 'required|numeric|min:0|max:100000',
            'per_kg_fee' => 'required|numeric|min:0|max:100000',
            'min_weight_kg' => 'required|numeric|min:0.01|max:1000',
            'max_weight_kg' => 'nullable|numeric|gte:min_weight_kg|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $data['name'] = $data['name'] ?? 'Standard';
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        return $data;
    }

    /**
     * @return array<int, string>
     */
    private function splitAreas(string $areas): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/[,;\n]+/', $areas) ?: [])));
    }

    private function ok(Request $request, string $message, object $model, ?string $redirect = null): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => $message, 'data' => $model]);
        }

        return redirect()->to($redirect ?? route('zones.index'))->with('toast', $message)->with('toast_type', 'success');
    }

    private function error(Request $request, ValidationException $e): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['ok' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        }

        return back()->withErrors($e->errors())->withInput();
    }
}
