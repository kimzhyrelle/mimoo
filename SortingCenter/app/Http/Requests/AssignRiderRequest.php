<?php

namespace App\Http\Requests;

use App\Models\Rider;
use Illuminate\Foundation\Http\FormRequest;

class AssignRiderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rider_id' => 'required|exists:riders,id',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $riderId = $this->input('rider_id');
            if ($riderId) {
                $rider = Rider::find($riderId);
                if (! $rider || ! $rider->isActive()) {
                    $validator->errors()->add('rider_id', 'Rider is inactive and cannot be assigned parcels.');
                } elseif (! $rider->isOnDuty()) {
                    $validator->errors()->add('rider_id', 'Rider is off duty and cannot be assigned parcels.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'rider_id.required' => 'A rider is required for assignment.',
            'rider_id.exists' => 'The selected rider does not exist.',
        ];
    }
}
