<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    /**
     * Show the master code entry form.
     *
     * Redirects directly to the dashboard if the session is already verified.
     */
    public function index(Request $request): Response|RedirectResponse
    {
        if ($request->session()->get('admin_verified', false)) {
            return redirect()->route('admin.dashboard');
        }

        return Inertia::render('admin/login');
    }

    /**
     * Verify the submitted master code against the value stored in .env.
     *
     * Uses a constant-time comparison to prevent timing attacks.
     * Returns a generic error message regardless of which part of the code was wrong.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $masterCode = config('app.admin_master_code');

        // hash_equals prevents timing-based side-channel attacks
        if (! hash_equals((string) $masterCode, (string) $request->input('code'))) {
            return back()->withErrors(['code' => 'Invalid code.']);
        }

        $request->session()->put('admin_verified', true);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    /**
     * Show the admin dashboard.
     */
    public function dashboard(): Response
    {
        return Inertia::render('admin/dashboard');
    }

    /**
     * Show the manage registrations page.
     */
    public function manageRegistrations(): Response
    {
        return Inertia::render('admin/manage-registrations');
    }

    /**
     * Clear the admin session flag and redirect back to the login form.
     */
    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('admin_verified');

        return redirect()->route('home');
    }
}
