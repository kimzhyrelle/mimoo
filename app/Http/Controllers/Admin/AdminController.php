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
     * Show the seller approval page.
     */
    public function sellerApproval(): Response
    {
        $registrations = \App\Models\User::query()
            ->where('account_type', 'seller')
            ->where('verification_status', 'verified') // Only show verified sellers
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
            'total' => \App\Models\User::where('account_type', 'seller')->where('verification_status', 'verified')->count(),
            'pending' => \App\Models\User::where('account_type', 'seller')->where('verification_status', 'verified')->where('status', 'pending')->count(),
            'approved' => \App\Models\User::where('account_type', 'seller')->where('verification_status', 'verified')->where('status', 'approved')->count(),
            'disapproved' => \App\Models\User::where('account_type', 'seller')->where('verification_status', 'verified')->where('status', 'disapproved')->count(),
        ];

        return Inertia::render('admin/seller-approval', [
            'registrations' => $registrations,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the product approval page.
     */
    public function productApproval(): Response
    {
        return Inertia::render('admin/product-approval');
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
     * Approve a seller registration.
     */
    public function approveSellerRegistration(Request $request, int $userId): RedirectResponse
    {
        $user = \App\Models\User::findOrFail($userId);
        
        if ($user->account_type !== 'seller') {
            return back()->withErrors(['error' => 'This account is not a seller.']);
        }
        
        $user->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        return back()->with('success', "Seller account for {$user->name} has been approved.");
    }

    /**
     * Disapprove a seller registration.
     */
    public function disapproveSellerRegistration(Request $request, int $userId): RedirectResponse
    {
        $user = \App\Models\User::findOrFail($userId);
        
        if ($user->account_type !== 'seller') {
            return back()->withErrors(['error' => 'This account is not a seller.']);
        }
        
        $user->update([
            'status' => 'disapproved',
            'approved_at' => null,
        ]);

        return back()->with('success', "Seller account for {$user->name} has been disapproved.");
    }

    /**
     * Get detailed information about a seller for review.
     */
    public function getSellerDetails(int $userId): \Illuminate\Http\JsonResponse
    {
        $user = \App\Models\User::findOrFail($userId);
        
        if ($user->account_type !== 'seller' && $user->account_type !== 'logistics') {
            return response()->json(['error' => 'User is not a seller or logistics provider.'], 400);
        }
        
        return response()->json([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'middle_initial' => $user->middle_initial,
            'name' => $user->name,
            'sex' => $user->sex,
            'email' => $user->email,
            'contact_number' => $user->contact_number,
            'birthday' => $user->birthday,
            'tin' => $user->tin,
            'account_type' => $user->account_type,
            'region' => $user->region,
            'province' => $user->province,
            'district' => $user->district,
            'municipality_city' => $user->municipality_city,
            'barangay' => $user->barangay,
            'postal_code' => $user->postal_code,
            'street_address' => $user->street_address,
            'business_name' => $user->business_name,
            'business_type' => $user->business_type,
            'line_of_business' => $user->line_of_business,
            'status' => $user->status,
            'verification_status' => $user->verification_status,
            'created_at' => $user->created_at->format('M d, Y g:i A'),
            'approved_at' => $user->approved_at?->format('M d, Y g:i A'),
            // Document URLs
            'id_document_url' => $user->id_document ? route('admin.download-document', ['userId' => $user->id, 'type' => 'id_document']) : null,
            'business_permit_url' => $user->business_permit ? route('admin.download-document', ['userId' => $user->id, 'type' => 'business_permit']) : null,
            'dti_registration_url' => $user->dti_registration ? route('admin.download-document', ['userId' => $user->id, 'type' => 'dti_registration']) : null,
            'bir_2303_url' => $user->bir_2303 ? route('admin.download-document', ['userId' => $user->id, 'type' => 'bir_2303']) : null,
            'sec_certificate_url' => $user->sec_certificate ? route('admin.download-document', ['userId' => $user->id, 'type' => 'sec_certificate']) : null,
        ]);
    }

    /**
     * Download a user's document (admin only).
     */
    public function downloadDocument(int $userId, string $type): \Symfony\Component\HttpFoundation\BinaryFileResponse|RedirectResponse
    {
        $user = \App\Models\User::findOrFail($userId);
        
        $allowedTypes = ['id_document', 'business_permit', 'dti_registration', 'bir_2303', 'sec_certificate'];
        
        if (!in_array($type, $allowedTypes)) {
            return back()->withErrors(['error' => 'Invalid document type.']);
        }
        
        $filePath = $user->{$type};
        
        if (!$filePath || !\Storage::disk('local')->exists($filePath)) {
            return back()->withErrors(['error' => 'Document not found.']);
        }
        
        return response()->download(storage_path('app/' . $filePath));
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