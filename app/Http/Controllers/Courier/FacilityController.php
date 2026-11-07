<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use App\Models\CourierFacility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class FacilityController extends Controller
{
    /**
     * Display facility management page
     */
    public function index()
    {
        $courier = Auth::user();
        
        $facilities = CourierFacility::where('courier_id', $courier->id)
            ->with('parentFacility')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($facility) {
                return [
                    'id' => $facility->id,
                    'facility_name' => $facility->facility_name,
                    'facility_code' => $facility->facility_code,
                    'facility_type' => $facility->facility_type,
                    'parent_facility' => $facility->parentFacility?->facility_name,
                    'status' => $facility->status,
                    'region' => $facility->region,
                    'province' => $facility->province,
                    'city' => $facility->city,
                    'address' => $facility->address,
                    'manager' => $facility->manager_name,
                    'contact' => $facility->contact_number,
                    'coverage' => $facility->coverage_cities,
                ];
            });

        return Inertia::render('courier/BranchFacility', [
            'auth' => [
                'user' => [
                    'id' => $courier->id,
                    'name' => $courier->first_name . ' ' . $courier->last_name,
                    'email' => $courier->email,
                    'account_type' => $courier->account_type,
                ],
            ],
            'company' => [
                'name' => $courier->company_name ?? $courier->business_name ?? 'Courier Company',
                'contact_person' => $courier->first_name . ' ' . $courier->last_name,
            ],
            'facilities' => $facilities,
        ]);
    }

    /**
     * Store a new facility
     */
    public function store(Request $request)
    {
        $courier = Auth::user();

        $validated = $request->validate([
            'facility_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('courier_facilities')->where(function ($query) use ($courier) {
                    return $query->where('courier_id', $courier->id);
                }),
            ],
            'facility_type' => ['required', 'in:Main Hub,Sorting Hub,Branch'],
            'parent_facility_id' => [
                'nullable',
                'exists:courier_facilities,id',
                function ($attribute, $value, $fail) use ($request, $courier) {
                    if ($request->facility_type === 'Main Hub' && $value) {
                        $fail('Main Hub cannot have a parent facility.');
                    }
                    
                    // Only require parent facility if there are existing facilities
                    $existingFacilities = \App\Models\CourierFacility::where('courier_id', $courier->id)->count();
                    
                    if ($request->facility_type !== 'Main Hub' && !$value && $existingFacilities > 0) {
                        $fail('This facility type requires a parent facility. Please create a Main Hub first or select an existing parent facility.');
                    }
                },
            ],
            'status' => ['required', 'in:Active,Inactive'],
            'region' => ['required', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'manager_name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'regex:/^(09|\+639)\d{9}$/'],
            'coverage_cities' => ['required', 'array', 'min:1'],
            'coverage_cities.*' => ['required', 'string'],
        ]);

        // Generate facility code
        $facilityCode = $this->generateFacilityCode($courier->id, $validated['facility_type']);

        $facility = CourierFacility::create([
            'courier_id' => $courier->id,
            'facility_name' => $validated['facility_name'],
            'facility_code' => $facilityCode,
            'facility_type' => $validated['facility_type'],
            'parent_facility_id' => $validated['parent_facility_id'],
            'status' => $validated['status'],
            'region' => $validated['region'],
            'province' => $validated['province'],
            'city' => $validated['city'],
            'address' => $validated['address'],
            'manager_name' => $validated['manager_name'],
            'contact_number' => $validated['contact_number'],
            'coverage_cities' => $validated['coverage_cities'],
        ]);

        return redirect()->back()->with('success', 'Facility created successfully.');
    }

    /**
     * Update an existing facility
     */
    public function update(Request $request, CourierFacility $facility)
    {
        $courier = Auth::user();

        // Ensure facility belongs to authenticated courier
        if ($facility->courier_id !== $courier->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'facility_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('courier_facilities')->where(function ($query) use ($courier) {
                    return $query->where('courier_id', $courier->id);
                })->ignore($facility->id),
            ],
            'facility_type' => ['required', 'in:Main Hub,Sorting Hub,Branch'],
            'parent_facility_id' => [
                'nullable',
                'exists:courier_facilities,id',
                function ($attribute, $value, $fail) use ($request, $facility, $courier) {
                    if ($request->facility_type === 'Main Hub' && $value) {
                        $fail('Main Hub cannot have a parent facility.');
                    }
                    
                    // Only require parent facility if there are other existing facilities
                    $existingFacilities = \App\Models\CourierFacility::where('courier_id', $courier->id)
                        ->where('id', '!=', $facility->id)
                        ->count();
                    
                    if ($request->facility_type !== 'Main Hub' && !$value && $existingFacilities > 0) {
                        $fail('This facility type requires a parent facility. Please create a Main Hub first or select an existing parent facility.');
                    }
                    
                    if ($value == $facility->id) {
                        $fail('A facility cannot be its own parent.');
                    }
                },
            ],
            'status' => ['required', 'in:Active,Inactive'],
            'region' => ['required', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'manager_name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'regex:/^(09|\+639)\d{9}$/'],
            'coverage_cities' => ['required', 'array', 'min:1'],
            'coverage_cities.*' => ['required', 'string'],
        ]);

        $facility->update($validated);

        return redirect()->back()->with('success', 'Facility updated successfully.');
    }

    /**
     * Delete a facility
     */
    public function destroy(CourierFacility $facility)
    {
        $courier = Auth::user();

        // Ensure facility belongs to authenticated courier
        if ($facility->courier_id !== $courier->id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if facility has child facilities
        if ($facility->childFacilities()->count() > 0) {
            return redirect()->back()->withErrors([
                'error' => 'Cannot delete facility with child facilities. Please reassign or delete child facilities first.',
            ]);
        }

        $facility->delete();

        return redirect()->back()->with('success', 'Facility deleted successfully.');
    }

    /**
     * Generate unique facility code
     */
    private function generateFacilityCode(int $courierId, string $facilityType): string
    {
        $prefix = match ($facilityType) {
            'Main Hub' => 'HUB',
            'Sorting Hub' => 'SRT',
            'Branch' => 'BRN',
            default => 'FAC',
        };

        $count = CourierFacility::where('courier_id', $courierId)
            ->where('facility_type', $facilityType)
            ->count();

        return $prefix . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }
}
