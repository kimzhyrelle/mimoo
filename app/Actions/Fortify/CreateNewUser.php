<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * Handles both buyer and seller registrations.  File uploads (id_document,
     * business_permit) are stored in the private `registrations` disk so they
     * are not publicly accessible without an explicit controller action.
     *
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            // ── Personal details ──────────────────────────────────────────
            'first_name'      => ['required', 'string', 'max:100'],
            'last_name'       => ['required', 'string', 'max:100'],
            'middle_initial'  => ['nullable', 'string', 'max:1'],
            'sex'             => ['required', 'in:male,female'],
            'email'           => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'contact_number'  => ['required', 'string', 'regex:/^09\d{9}$/'],
            'birthday'        => ['required', 'date', 'before:-18 years'],
            'account_type'    => ['required', 'in:buyer,seller'],

            // ── Address ───────────────────────────────────────────────────
            'province'         => ['required', 'string', 'max:100'],
            'municipality_city'=> ['required', 'string', 'max:100'],
            'barangay'         => ['required', 'string', 'max:100'],
            'street_address'   => ['required', 'string', 'max:255'],

            // ── Identity document (required for everyone) ─────────────────
            'id_document'     => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],

            // ── Seller-only fields ────────────────────────────────────────
            'business_name'    => ['required_if:account_type,seller', 'nullable', 'string', 'max:255'],
            'line_of_business' => ['required_if:account_type,seller', 'nullable', 'string', 'max:100'],
            'business_permit'  => ['required_if:account_type,seller', 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],

            // ── Security ──────────────────────────────────────────────────
            'password'        => $this->passwordRules(),
        ], [
            'birthday.before'          => 'You must be at least 18 years old to register.',
            'contact_number.regex'     => 'Contact number must be an 11-digit mobile number starting with 09.',
            'id_document.required'     => 'Please upload a valid government-issued ID.',
            'business_permit.required_if' => 'A business permit is required for seller accounts.',
        ])->validate();

        // ── Store uploaded files ───────────────────────────────────────────
        $idDocumentPath    = $this->storeFile($input['id_document'], 'id_documents');
        $businessPermitPath = isset($input['business_permit']) && $input['business_permit'] instanceof UploadedFile
            ? $this->storeFile($input['business_permit'], 'business_permits')
            : null;

        // ── Create user ────────────────────────────────────────────────────
        return User::create([
            // Computed full name for backward-compat with anything that reads `name`
            'name'              => trim($input['first_name'].' '.$input['last_name']),
            'first_name'        => $input['first_name'],
            'last_name'         => $input['last_name'],
            'middle_initial'    => $input['middle_initial'] ?? null,
            'sex'               => $input['sex'],
            'email'             => $input['email'],
            'contact_number'    => $input['contact_number'],
            'birthday'          => $input['birthday'],
            'account_type'      => $input['account_type'],
            'province'          => $input['province'],
            'municipality_city' => $input['municipality_city'],
            'barangay'          => $input['barangay'],
            'street_address'    => $input['street_address'],
            'id_document'       => $idDocumentPath,
            'business_name'     => $input['business_name'] ?? null,
            'line_of_business'  => $input['line_of_business'] ?? null,
            'business_permit'   => $businessPermitPath,
            'password'          => $input['password'],
            // Buyers are auto-approved, sellers need admin review
            'status'            => $input['account_type'] === 'buyer' ? 'approved' : 'pending',
            'approved_at'       => $input['account_type'] === 'buyer' ? now() : null,
        ]);
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
