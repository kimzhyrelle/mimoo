<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'entity_name' => trim((string) ($this->input('entity_name') ?: $this->input('name', ''))),
            'first_name' => trim((string) $this->input('first_name', '')),
            'last_name' => trim((string) $this->input('last_name', '')),
            'middle_initial' => filled($this->input('middle_initial')) ? trim((string) $this->input('middle_initial')) : null,
            'staff_title' => filled($this->input('staff_title')) ? trim((string) $this->input('staff_title')) : null,
            'contact_email' => filled($this->input('contact_email')) ? trim((string) $this->input('contact_email')) : null,
            'contact_phone' => filled($this->input('contact_phone')) ? trim((string) $this->input('contact_phone')) : null,
            'address' => filled($this->input('address')) ? trim((string) $this->input('address')) : null,
            'coverage_note' => filled($this->input('coverage_note')) ? trim((string) $this->input('coverage_note')) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'entity_name' => ['required', 'string', 'min:2', 'max:255'],
            'first_name' => ['required', 'string', 'min:2', 'max:50'],
            'last_name' => ['required', 'string', 'min:2', 'max:50'],
            'middle_initial' => ['nullable', 'string', 'max:5'],
            'staff_title' => ['nullable', 'string', 'max:100'],
            'birthday' => [
                'nullable',
                'date',
                'before_or_equal:'.now()->subYears(18)->toDateString(),
            ],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => [
                'nullable',
                'string',
                'max:20',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $digits = preg_replace('/\D/', '', (string) $value);
                    $mobile = preg_match('/^09\d{9}$/', $digits) === 1;
                    $landline = preg_match('/^0[2-8]\d{8}$/', $digits) === 1;

                    if (! $mobile && ! $landline) {
                        $fail('Enter a valid PH mobile (09XX XXX XXXX) or landline ((049) 123-4567) number.');
                    }
                },
            ],
            'address' => ['nullable', 'string', 'max:255'],
            'coverage_note' => ['nullable', 'string', 'max:1000'],
            'confirm' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'entity_name.required' => 'The registered entity name is required.',
            'first_name.required' => 'The contact person first name is required.',
            'last_name.required' => 'The contact person last name is required.',
            'birthday.before_or_equal' => 'The contact person must be at least 18 years old.',
            'contact_phone.regex' => 'Enter a valid PH mobile (09XX XXX XXXX) or landline ((049) 123-4567) number.',
        ];
    }
}
