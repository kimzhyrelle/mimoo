<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\HubProfile;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function edit()
    {
        return view('sorting.account', [
            'hub'   => HubProfile::current(),
            'areas' => Area::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request)
    {
        // Strip spaces from phone before validation
        if ($request->has('contact_phone')) {
            $request->merge([
                'contact_phone' => preg_replace('/\s+/', '', $request->contact_phone),
            ]);
        }

        $data = $request->validate([
            'first_name'    => 'required|string|min:2|max:50',
            'last_name'     => 'required|string|min:2|max:50',
            'middle_initial'=> 'nullable|string|max:5',
            'staff_title'   => 'nullable|string|max:100',
            'birthday'      => ['nullable', 'date', 'before_or_equal:' . now()->subYears(18)->toDateString()],
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => ['nullable', 'string', 'regex:/^09\d{9}$/'],
            'address'       => 'nullable|string|max:255',
            'name'          => 'nullable|string|max:255',
            'coverage_note' => 'nullable|string|max:1000',
        ]);

        // Format phone for storage: 09XX XXX XXXX
        if (!empty($data['contact_phone'])) {
            $n = preg_replace('/\D/', '', $data['contact_phone']);
            $data['contact_phone'] = substr($n,0,4).' '.substr($n,4,3).' '.substr($n,7,4);
        }

        // Build staff_name and staff_initials from the name fields
        $firstName = trim($data['first_name']);
        $lastName  = trim($data['last_name']);
        $data['staff_name']     = $firstName . ' ' . $lastName;
        $data['staff_initials'] = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));

        HubProfile::current()->update($data);

        return back()->with('status', 'Profile updated successfully.');
    }
}
