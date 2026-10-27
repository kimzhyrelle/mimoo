<?php

namespace App\Http\Controllers;

use App\Models\Parcel;
use App\Models\Rider;
use App\Services\DispatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DispatchController extends Controller
{
    public function __construct(private DispatchService $dispatch) {}

    public function assign(Request $request, Parcel $parcel): JsonResponse|RedirectResponse
    {
        return $this->run($request, fn () => $this->dispatch->assign(
            $parcel,
            Rider::findOrFail($request->validate(['rider_id' => 'required|exists:riders,id'])['rider_id']),
            $request->user(),
            $request->input('reason'),
        ), "Parcel {$parcel->tracking_no} assigned.");
    }

    public function autoAssign(Request $request, Parcel $parcel): JsonResponse|RedirectResponse
    {
        return $this->run($request, function () use ($parcel) {
            $rider = $this->dispatch->autoAssign($parcel);

            if (! $rider) {
                throw ValidationException::withMessages(['rider_id' => 'No eligible rider with a free job slot for this area.']);
            }

            return "Parcel {$parcel->tracking_no} auto-assigned to {$rider->name}.";
        });
    }

    public function reassign(Request $request, Parcel $parcel): JsonResponse|RedirectResponse
    {
        return $this->run($request, fn () => $this->dispatch->reassign(
            $parcel,
            Rider::findOrFail($request->validate(['rider_id' => 'required|exists:riders,id'])['rider_id']),
            $request->user(),
        ), "Parcel {$parcel->tracking_no} reassigned.");
    }

    public function bulk(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
            'rider_id' => 'required|exists:riders,id',
        ]);

        try {
            $result = $this->dispatch->bulkAssign($data['ids'], Rider::findOrFail($data['rider_id']), $request->user());
        } catch (ValidationException $e) {
            return $this->error($request, $e);
        }

        $message = "{$result['assigned']} parcel(s) assigned.";

        if ($result['skipped'] !== []) {
            $message .= ' '.count($result['skipped']).' skipped: '.collect($result['skipped'])->pluck('reason')->unique()->implode(' ');
        }

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => $message, 'data' => $result]);
        }

        return back()->with('toast', $message)->with('toast_type', 'success')->with('skipped', $result['skipped']);
    }

    private function run(Request $request, callable $callback, ?string $success = null): JsonResponse|RedirectResponse
    {
        try {
            $result = $callback();
        } catch (ValidationException $e) {
            return $this->error($request, $e);
        }

        $message = is_string($result) ? $result : ($success ?? 'Done.');

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }

        return back()->with('toast', $message)->with('toast_type', 'success');
    }

    private function error(Request $request, ValidationException $e): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['ok' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        }

        return back()->withErrors($e->errors())->withInput()->with('toast', $e->getMessage())->with('toast_type', 'error');
    }
}
