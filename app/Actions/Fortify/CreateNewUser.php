<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Models\EmailVerification;
use App\Models\User;
use App\Notifications\EmailVerificationNotification;
use App\Rules\PhilippineMobile;
use App\Rules\StrongPassword;
use App\Rules\ValidName;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * Handles both buyer and seller registrations. File uploads (id_document,
     * business_permit) are stored in the private `registrations` disk so they
     * are not publicly accessible without an explicit controller action.
     *
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        // Trim email and normalize mobile number before validation
        $input['email'] = trim($input['email'] ?? '');
        $input['contact_number'] = PhilippineMobile::normalize($input['contact_number'] ?? '');
        
        // Trim names
        $input['first_name'] = trim($input['first_name'] ?? '');
        $input['last_name'] = trim($input['last_name'] ?? '');
        
        // Check if terms accepted (required for buyers)
        if (($input['account_type'] ?? 'buyer') === 'buyer' && empty($input['terms_accepted'])) {
            throw ValidationException::withMessages([
                'terms_accepted' => 'You must accept the Terms and Privacy Policy.',
            ]);
        }
        
        $validator = Validator::make($input, [
            // ── Personal details ──────────────────────────────────────────
            'first_name'      => ['required', 'string', new ValidName()],
            'last_name'       => ['required', 'string', new ValidName()],
            'middle_initial'  => ['nullable', 'string', 'max:2'],
            'sex'             => ['required', 'in:male,female'],
            'email'           => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'contact_number'  => ['required', 'string', new PhilippineMobile(), 'unique:users,contact_number'],
            'birthday'        => ['required', 'date', 'before:-18 years'],
            'account_type'    => ['required', 'in:buyer,seller,logistics'],
            'terms_accepted'  => ['required_if:account_type,buyer', 'accepted'],

            // ── Address ───────────────────────────────────────────────────
            'region'           => ['required', 'string', 'max:100'],
            'province'         => ['required', 'string', 'max:100'],
            'district'         => ['nullable', 'string', 'max:100'], // NCR only
            'municipality_city'=> ['required', 'string', 'max:100'],
            'barangay'         => ['required', 'string', 'max:100'],
            'street_address'   => ['required', 'string', 'max:255'],

            // ── Identity document (required for everyone) ─────────────────
            'id_document'     => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],

            // ── Seller-only fields ────────────────────────────────────────
            'business_name'    => ['required_if:account_type,seller,logistics', 'nullable', 'string', 'max:255'],
            'line_of_business' => ['required_if:account_type,seller', 'nullable', 'string', 'max:100'],
            'business_permit'  => ['required_if:account_type,seller,logistics', 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],

            // ── Security ──────────────────────────────────────────────────
            'password'        => ['required', 'string', new StrongPassword(), 'confirmed'],
        ], [
            'birthday.before'          => 'You must be at least 18 years old.',
            'contact_number.unique'    => 'Mobile number is already in use.',
            'email.unique'             => 'This email is already registered.',
            'id_document.required'     => 'Please upload a valid government-issued ID.',
            'business_permit.required_if' => 'A business permit is required for seller and logistics accounts.',
            'terms_accepted.required_if' => 'You must accept the Terms and Privacy Policy.',
            'terms_accepted.accepted' => 'You must accept the Terms and Privacy Policy.',
        ]);
        
        $validator->validate();

        // Use transaction for atomicity
        return DB::transaction(function () use ($input) {
            // ── Store uploaded files ───────────────────────────────────────────
            $idDocumentPath    = $this->storeFile($input['id_document'], 'id_documents');
            $businessPermitPath = isset($input['business_permit']) && $input['business_permit'] instanceof UploadedFile
                ? $this->storeFile($input['business_permit'], 'business_permits')
                : null;

            // ── Create user ────────────────────────────────────────────────────
            $user = User::create([
                // Computed full name for backward-compat with anything that reads `name`
                'name'              => trim($input['first_name'].' '.$input['last_name']),
                'first_name'        => $input['first_name'],
                'last_name'         => $input['last_name'],
                'middle_initial'    => $input['middle_initial'] ?? null,
                'sex'               => $input['sex'],
                'email'             => $input['email'],
                'contact_number'    => $input['contact_number'], // Already normalized
                'birthday'          => $input['birthday'],
                'account_type'      => $input['account_type'],
                'region'            => $input['region'],
                'province'          => $input['province'],
                'district'          => $input['district'] ?? null,
                'municipality_city' => $input['municipality_city'],
                'barangay'          => $input['barangay'],
                'street_address'    => $input['street_address'],
                'id_document'       => $idDocumentPath,
                'business_name'     => $input['business_name'] ?? null,
                'line_of_business'  => $input['line_of_business'] ?? null,
                'business_permit'   => $businessPermitPath,
                'password'          => $input['password'],
                // All accounts start as pending and unverified
                'status'            => 'pending',
                'verification_status' => 'unverified',
                'approved_at'       => null,
            ]);
            
            // ── Create OTP and send verification email ────────────────────────
            $verification = EmailVerification::createForEmail($user->email);
            
            // Send OTP email
            Notification::route('mail', $user->email)
                ->notify(new EmailVerificationNotification($verification->code));
            
            return $user;
        });
    }

    /**
     * Store an uploaded file and return its path relative to the storage disk.
     */
    private function storeFile(mixed $file, string $directory): ?string
    {
        if (! $file instanceof UploadedFile) {
            return null;
        }

        return $file->store($directory, 'local');
    }
}
