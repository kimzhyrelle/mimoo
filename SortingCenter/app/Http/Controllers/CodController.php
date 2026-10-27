<?php

namespace App\Http\Controllers;

use App\Models\CodRemittance;
use App\Models\Parcel;
use App\Models\Rider;
use App\Services\AuditLogger;
use App\Support\CompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CodController extends Controller
{
    public function index(): View
    {
        $pending = Parcel::with('rider')
            ->where('status', 'delivered')
            ->where('is_prepaid', false)
            ->where('cod_collected', '>', 0)
            ->where('cod_remitted', false)
            ->orderBy('delivered_at')
            ->get();

        $riders = $pending->groupBy('rider_id')->map(function ($parcels) {
            return [
                'rider' => $parcels->first()->rider,
                'parcels' => $parcels,
                'expected' => (float) $parcels->sum(fn (Parcel $parcel) => (float) $parcel->cod_collected),
            ];
        })->filter(fn ($row) => $row['rider'] !== null)->values();

        return view('sorting.cod.index', [
            'groups' => $riders,
            'remittances' => CodRemittance::with('rider', 'confirmedBy')->orderByDesc('created_at')->limit(50)->get(),
            'unassigned' => $pending->whereNull('rider_id'),
        ]);
    }

    public function store(Request $request, AuditLogger $audit): JsonResponse|RedirectResponse
    {
        try {
            $data = $request->validate([
                'rider_id' => 'required|exists:riders,id',
                'collected' => 'required|numeric|min:0|max:10000000',
                'reference' => 'required|string|min:3|max:80',
            ]);
        } catch (ValidationException $e) {
            return $this->error($request, $e);
        }

        $rider = Rider::findOrFail($data['rider_id']);
        $expected = (float) Parcel::where('rider_id', $rider->id)
            ->where('status', 'delivered')
            ->where('is_prepaid', false)
            ->where('cod_collected', '>', 0)
            ->where('cod_remitted', false)
            ->sum('cod_collected');

        if ($expected <= 0) {
            return $this->error($request, ValidationException::withMessages(['rider_id' => 'This rider has no unremitted COD collections.']));
        }

        $existing = CodRemittance::where('rider_id', $rider->id)
            ->where('reference', $data['reference'])
            ->first();

        if ($existing) {
            return $this->ok($request, "Remittance {$existing->reference} already recorded.", $existing, route('cod.index'));
        }

        $remittance = CodRemittance::create([
            'company_id' => CompanyContext::id(),
            'rider_id' => $rider->id,
            'amount' => $expected,
            'collected' => $data['collected'],
            'difference' => round((float) $data['collected'] - $expected, 2),
            'status' => 'pending',
            'reference' => $data['reference'],
        ]);

        $audit->log('cod.remittance_recorded', $remittance, [], ['amount' => $expected, 'collected' => $data['collected']], 'COD remittance recorded');

        return $this->ok($request, 'Remittance recorded. Confirm it to update the rider balance.', $remittance, route('cod.index'));
    }

    public function confirm(Request $request, CodRemittance $remittance, AuditLogger $audit): JsonResponse|RedirectResponse
    {
        if ($remittance->status === 'confirmed') {
            return $this->ok($request, 'Remittance already confirmed.', $remittance, route('cod.index'));
        }

        DB::transaction(function () use ($remittance, $audit) {
            $rider = $remittance->rider;
            $parcels = Parcel::where('rider_id', $rider->id)
                ->where('status', 'delivered')
                ->where('is_prepaid', false)
                ->where('cod_collected', '>', 0)
                ->where('cod_remitted', false)
                ->get();

            foreach ($parcels as $parcel) {
                $parcel->update(['cod_remitted' => true, 'cod_remitted_at' => now()]);
            }

            $rider->update([
                'cod_balance' => max(0, (float) $rider->cod_balance - (float) $remittance->collected + (float) $remittance->difference),
            ]);

            $remittance->update([
                'status' => 'confirmed',
                'confirmed_by' => auth()->id(),
                'confirmed_at' => now(),
            ]);

            $audit->log('cod.remittance_confirmed', $remittance, ['status' => 'pending'], [
                'status' => 'confirmed',
                'collected' => (float) $remittance->collected,
                'difference' => (float) $remittance->difference,
                'parcels' => $parcels->count(),
            ], 'COD remitted and rider balance reconciled', auth()->id());
        });

        return $this->ok($request, 'Remittance confirmed. Rider COD balance updated.', $remittance->fresh(), route('cod.index'));
    }

    private function ok(Request $request, string $message, object $data, string $redirect): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => $message, 'data' => $data]);
        }

        return redirect()->to($redirect)->with('toast', $message)->with('toast_type', 'success');
    }

    private function error(Request $request, ValidationException $e): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['ok' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        }

        return back()->withErrors($e->errors())->withInput();
    }
}
