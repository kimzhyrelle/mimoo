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
            'hub' => HubProfile::current(),
            'areas' => Area::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'coverage_note' => 'nullable|string|max:1000',
        ]);

        HubProfile::current()->update($data);

        return back()->with('status', 'Hub profile updated.');
    }
}
