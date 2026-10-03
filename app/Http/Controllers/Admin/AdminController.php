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
        $inputCode = $request->input('code');

        if (! hash_equals((string) $masterCode, (string) $inputCode)) {
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
        $registrations = \App\Models\User::query()
            ->whereIn('status', ['pending', 'approved', 'disapproved'])
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'disapproved')")
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'account_type' => $user->account_type,
                'business_name' => $user->business_name,
                'status' => $user->status,
                'created_at' => $user->created_at->format('M d, Y'),
                'approved_at' => $user->approved_at?->format('M d, Y'),
            ]);

        $stats = [
            'total' => \App\Models\User::count(),
            'pending' => \App\Models\User::where('status', 'pending')->count(),
            'approved' => \App\Models\User::where('status', 'approved')->count(),
            'disapproved' => \App\Models\User::where('status', 'disapproved')->count(),
        ];

        return Inertia::render('admin/manage-registrations', [
            'registrations' => $registrations,
            'stats' => $stats,
        ]);
    }

    /**
     * Approve a user registration.
     */
    public function approveRegistration(Request $request, int $userId): RedirectResponse
    {
        $user = \App\Models\User::findOrFail($userId);
        
        $user->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        return back()->with('success', "Account for {$user->name} has been approved.");
    }

    /**
     * Disapprove a user registration.
     */
    public function disapproveRegistration(Request $request, int $userId): RedirectResponse
    {
        $user = \App\Models\User::findOrFail($userId);
        
        $user->update([
            'status' => 'disapproved',
            'approved_at' => null,
        ]);

        return back()->with('success', "Account for {$user->name} has been disapproved.");
    }

    /**
     * Clear the admin session flag and redirect to the landing page.
     */
    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('admin_verified');

        return redirect()->route('home');
    }
}