<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VoucherController extends Controller
{
    public function index()
    {
        $vouchers = Voucher::withCount('usages')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($voucher) {
                return [
                    'id' => $voucher->id,
                    'code' => $voucher->code,
                    'name' => $voucher->name,
                    'description' => $voucher->description,
                    'type' => $voucher->type,
                    'value' => $voucher->value,
                    'min_purchase' => $voucher->min_purchase,
                    'max_discount' => $voucher->max_discount,
                    'usage_limit' => $voucher->usage_limit,
                    'usage_per_user' => $voucher->usage_per_user,
                    'usage_count' => $voucher->usage_count,
                    'usages_count' => $voucher->usages_count,
                    'valid_from' => $voucher->valid_from->format('M d, Y H:i'),
                    'valid_until' => $voucher->valid_until->format('M d, Y H:i'),
                    'status' => $voucher->status,
                    'terms_conditions' => $voucher->terms_conditions,
                    'implemented_by' => $voucher->implemented_by,
                    'created_at' => $voucher->created_at->format('M d, Y'),
                    'is_valid' => $voucher->isValid(),
                ];
            });

        return Inertia::render('admin/Vouchers', [
            'vouchers' => $vouchers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:vouchers,code|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_purchase' => 'required|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_per_user' => 'required|integer|min:1',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'terms_conditions' => 'nullable|array',
            'implemented_by' => 'nullable|string|max:255',
        ]);

        $voucher = Voucher::create($validated);

        return redirect()->back()->with('success', 'Voucher created successfully!');
    }

    public function update(Request $request, Voucher $voucher)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:vouchers,code,' . $voucher->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_purchase' => 'required|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_per_user' => 'required|integer|min:1',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'status' => 'required|in:active,inactive,expired',
            'terms_conditions' => 'nullable|array',
            'implemented_by' => 'nullable|string|max:255',
        ]);

        $voucher->update($validated);

        return redirect()->back()->with('success', 'Voucher updated successfully!');
    }

    public function destroy(Voucher $voucher)
    {
        // Check if voucher has been used
        if ($voucher->usage_count > 0) {
            return redirect()->back()->with('error', 'Cannot delete voucher that has been used.');
        }

        $voucher->delete();

        return redirect()->back()->with('success', 'Voucher deleted successfully!');
    }

    public function toggleStatus(Voucher $voucher)
    {
        $newStatus = $voucher->status === 'active' ? 'inactive' : 'active';
        $voucher->update(['status' => $newStatus]);

        return redirect()->back()->with('success', 'Voucher status updated!');
    }

    // Validate voucher code (for frontend)
    public function validate(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'order_total' => 'required|numeric|min:0',
        ]);

        $voucher = Voucher::where('code', strtoupper($request->code))->first();

        if (!$voucher) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid voucher code',
            ], 404);
        }

        if (!$voucher->isValid()) {
            return response()->json([
                'valid' => false,
                'message' => 'This voucher has expired or is no longer available',
            ], 400);
        }

        if (!$voucher->canBeUsedBy(auth()->user())) {
            return response()->json([
                'valid' => false,
                'message' => 'You have already used this voucher the maximum number of times',
            ], 400);
        }

        if ($request->order_total < $voucher->min_purchase) {
            return response()->json([
                'valid' => false,
                'message' => "Minimum purchase of ₱" . number_format($voucher->min_purchase, 2) . " required",
            ], 400);
        }

        $discount = $voucher->calculateDiscount($request->order_total);

        return response()->json([
            'valid' => true,
            'voucher' => [
                'id' => $voucher->id,
                'code' => $voucher->code,
                'name' => $voucher->name,
                'type' => $voucher->type,
                'value' => $voucher->value,
                'discount_amount' => $discount,
            ],
            'message' => 'Voucher applied successfully!',
        ]);
    }
}
