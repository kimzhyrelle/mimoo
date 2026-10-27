<?php

namespace App\Http\Controllers;

use App\Models\Parcel;
use App\Services\ParcelStatusService;
use App\Services\ShipmentStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ShipmentController extends Controller
{
    public function __construct(
        protected ParcelStatusService $statusService,
        protected ShipmentStatusService $shipments,
    ) {}

    /**
     * POST /shipments/status or POST /shipments/{parcel}/status
     * Route name: shipments.status
     */
    public function updateStatus(Request $request, ?Parcel $parcel = null): JsonResponse|RedirectResponse
    {
        $parcelId = $parcel?->id ?? $request->input('parcel_id') ?? $request->input('id');
        $parcel = $parcel ?: Parcel::findOrFail($parcelId);

        $request->validate([
            'status' => 'required|string',
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            $result = $this->statusService->transition(
                parcel: $parcel,
                newStatus: $request->input('status'),
                reason: $request->input('reason'),
                user: $request->user()
            );

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'ok' => true,
                    'message' => $result['message'],
                    'undo_token' => $result['undo_token'],
                    'parcel' => $result['parcel'],
                ]);
            }

            $redirect = back()
                ->with('status', $result['message'])
                ->with('toast', $result['message']);

            if (! empty($result['undo_token'])) {
                $redirect
                    ->with('toast_undo', 'Undo')
                    ->with('toast_undo_url', route('shipments.undo', ['token' => $result['undo_token']]));
            }

            return $redirect;
        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }
    }

    public function override(Request $request, Parcel $parcel): JsonResponse|RedirectResponse
    {
        try {
            $data = $request->validate([
                'status' => 'required|string|in:'.implode(',', array_keys(ShipmentStatusService::ALLOWED)),
                'reason' => 'required|string|min:10|max:300',
            ]);
            $result = $this->shipments->override($parcel, $data['status'], $data['reason'], $request->user());
        } catch (ValidationException $e) {
            return $this->respondError($request, $e);
        }

        return $this->respondResult($request, $result);
    }

    public function failDelivery(Request $request, Parcel $parcel): JsonResponse|RedirectResponse
    {
        try {
            $data = $request->validate([
                'reason' => 'required|string|in:'.implode(',', ShipmentStatusService::REASONS),
            ]);
            $result = $this->shipments->failDelivery($parcel, $data['reason'], $request->user());
        } catch (ValidationException $e) {
            return $this->respondError($request, $e);
        }

        return $this->respondResult($request, $result);
    }

    public function lost(Request $request, Parcel $parcel): JsonResponse|RedirectResponse
    {
        try {
            $data = $request->validate(['reason' => 'required|string|min:10|max:300']);
            $result = $this->shipments->markLost($parcel, $data['reason'], $request->user());
        } catch (ValidationException $e) {
            return $this->respondError($request, $e);
        }

        return $this->respondResult($request, $result);
    }

    public function damaged(Request $request, Parcel $parcel): JsonResponse|RedirectResponse
    {
        try {
            $data = $request->validate([
                'reason' => 'required|string|min:10|max:300',
                'photo' => 'required|file|image|max:5120',
            ]);
            $path = $request->file('photo')->store('damage-photos', 'public');
            $result = $this->shipments->markDamaged($parcel, $data['reason'], $path, $request->user());
        } catch (ValidationException $e) {
            return $this->respondError($request, $e);
        }

        return $this->respondResult($request, $result);
    }

    public function sellerReceipt(Request $request, Parcel $parcel): JsonResponse|RedirectResponse
    {
        try {
            $result = $this->shipments->confirmSellerReceipt($parcel);
        } catch (ValidationException $e) {
            return $this->respondError($request, $e);
        }

        $message = "Return for {$parcel->tracking_no} confirmed. Stock restored".($parcel->is_prepaid ? ' and prepaid amount refunded' : '').'.';

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => $message, 'parcel' => $result['parcel']]);
        }

        return back()->with('toast', $message);
    }

    /**
     * POST /shipments/undo or POST /shipments/undo/{token?}
     * Route name: shipments.undo
     */
    public function undo(Request $request, ?string $token = null): JsonResponse|RedirectResponse
    {
        $token = $token ?: $request->input('token') ?: $request->query('token');

        if (! $token) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Undo token is required.',
                ], 422);
            }

            return back()->withErrors(['token' => 'Undo token is required.']);
        }

        try {
            $result = $this->statusService->undo($token);

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'ok' => true,
                    'message' => $result['message'],
                    'parcel' => $result['parcel'],
                ]);
            }

            return back()
                ->with('status', $result['message'])
                ->with('toast', $result['message'])
                ->with('toast_type', 'info');
        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }
    }

    /**
     * POST /shipments/{parcel}/archive or POST /shipments/archive
     * Route name: shipments.archive
     */
    public function archive(Request $request, ?Parcel $parcel = null): JsonResponse|RedirectResponse
    {
        $parcelId = $parcel?->id ?? $request->input('parcel_id') ?? $request->input('id');
        $parcel = $parcel ?: Parcel::findOrFail($parcelId);

        $result = $this->statusService->archive($parcel, $request->user());

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $result['message'],
                'parcel' => $result['parcel'],
            ]);
        }

        return back()
            ->with('status', $result['message'])
            ->with('toast', $result['message'])
            ->with('toast_undo', 'Restore')
            ->with('toast_undo_url', route('shipments.restore', ['parcel_id' => $parcel->id]));
    }

    /**
     * POST /parcels/sort/bulk-archive
     * Route name: parcels.sort.bulk-archive
     */
    public function bulkArchive(Request $request): JsonResponse|RedirectResponse
    {
        $ids = $request->input('ids') ?? $request->input('parcel_ids');
        if (is_string($ids)) {
            $ids = array_filter(array_map('trim', explode(',', $ids)));
        }

        $ids = (array) $ids;

        if (empty($ids)) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => 'No parcels selected.'], 422);
            }

            return back()->withErrors('No parcels selected to archive.');
        }

        $count = 0;
        foreach ($ids as $id) {
            $parcel = Parcel::find($id);
            if ($parcel) {
                $this->statusService->archive($parcel, $request->user());
                $count++;
            }
        }

        $msg = "{$count} ".($count === 1 ? 'parcel' : 'parcels').' archived.';

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $msg,
                'count' => $count,
            ]);
        }

        return back()->with('status', $msg)->with('toast', $msg);
    }

    /**
     * POST /shipments/{parcel}/restore or POST /shipments/restore
     * Route name: shipments.restore
     */
    public function restore(Request $request, $parcel = null): JsonResponse|RedirectResponse
    {
        $parcelId = is_object($parcel) ? $parcel->id : ($parcel ?: $request->input('parcel_id') ?: $request->input('id'));
        $target = Parcel::onlyTrashed()->findOrFail($parcelId);

        $result = $this->statusService->restore($target);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $result['message'],
                'parcel' => $result['parcel'],
            ]);
        }

        return back()
            ->with('status', $result['message'])
            ->with('toast', $result['message']);
    }

    private function respondResult(Request $request, array $result): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $result['message'],
                'parcel' => $result['parcel'] ?? null,
                'attempts' => $result['attempts'] ?? null,
            ]);
        }

        return back()->with('toast', $result['message'])->with('toast_type', 'success');
    }

    private function respondError(Request $request, ValidationException $e): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }

        return back()->withErrors($e->errors())->withInput()->with('toast', $e->getMessage())->with('toast_type', 'error');
    }
}
