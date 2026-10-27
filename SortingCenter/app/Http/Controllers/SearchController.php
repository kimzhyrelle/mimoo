<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Parcel;
use App\Models\Rider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $term = trim((string) ($validated['q'] ?? ''));

        if ($term === '') {
            return response()->json([
                'query' => '',
                'parcels' => [],
                'riders' => [],
                'sellers' => [],
                'branches' => [],
            ]);
        }

        $like = '%'.addcslashes($term, '\\%_').'%';

        $parcels = Parcel::query()
            ->with('area')
            ->where(function ($query) use ($like) {
                $query->where('tracking_no', 'like', $like)
                    ->orWhere('buyer_name', 'like', $like)
                    ->orWhere('seller_name', 'like', $like)
                    ->orWhere('address', 'like', $like);
            })
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Parcel $parcel) => [
                'id' => $parcel->id,
                'tracking_no' => $parcel->tracking_no,
                'title' => $parcel->tracking_no.' - '.($parcel->buyer_name ?: $parcel->address),
                'subtitle' => collect([$parcel->seller_name, $parcel->area?->name, $parcel->statusLabel()])
                    ->filter()
                    ->implode(' - '),
                'status' => $parcel->status,
                'url' => route('delivery.monitoring', ['q' => $parcel->tracking_no]),
            ]);

        $riders = Rider::query()
            ->with(['area', 'branch'])
            ->where(function ($query) use ($like) {
                $query->where('name', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('plate_no', 'like', $like)
                    ->orWhereHas('branch', fn ($query) => $query
                        ->where('code', 'like', $like)
                        ->orWhere('name', 'like', $like));
            })
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->map(fn (Rider $rider) => [
                'id' => $rider->id,
                'title' => $rider->name,
                'subtitle' => collect([$rider->branch?->code, $rider->vehicleLabel(), $rider->area?->name, $rider->statusLabel()])
                    ->filter()
                    ->implode(' - '),
                'initials' => $rider->initials,
                'url' => route('riders.index', ['q' => $rider->name]),
            ]);

        $sellers = Parcel::query()
            ->whereNotNull('seller_name')
            ->where('seller_name', 'like', $like)
            ->select('seller_name')
            ->distinct()
            ->orderBy('seller_name')
            ->limit(5)
            ->pluck('seller_name')
            ->map(fn (string $seller) => [
                'title' => $seller,
                'subtitle' => 'Seller',
                'url' => route('delivery.monitoring', ['q' => $seller]),
            ]);

        $branches = Branch::query()
            ->where(function ($query) use ($like) {
                $query->where('code', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhere('address', 'like', $like);
            })
            ->orderBy('code')
            ->limit(5)
            ->get()
            ->map(fn (Branch $branch) => [
                'id' => $branch->id,
                'title' => $branch->code.' - '.$branch->name,
                'subtitle' => collect([$branch->typeLabel(), $branch->address])->filter()->implode(' - '),
                'url' => route('branches.index', ['q' => $branch->code]),
            ]);

        return response()->json([
            'query' => $term,
            'parcels' => $parcels,
            'riders' => $riders,
            'sellers' => $sellers,
            'branches' => $branches,
        ]);
    }
}
