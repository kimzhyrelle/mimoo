<?php

namespace App\Http\Requests;

use App\Models\Parcel;
use App\Models\Rider;
use Illuminate\Foundation\Http\FormRequest;

class BulkAssignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->parcel_ids)) {
            $this->merge([
                'parcel_ids' => array_filter(array_map('trim', explode(',', $this->parcel_ids))),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'rider_id' => 'required|exists:riders,id',
            'parcel_ids' => 'required|array|min:1',
            'parcel_ids.*' => 'required|exists:parcels,id',
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

            $parcelIds = $this->input('parcel_ids');
            if (is_array($parcelIds) && ! empty($parcelIds)) {
                $parcels = Parcel::whereIn('id', $parcelIds)->get();

                foreach ($parcels as $parcel) {
                    if ($parcel->isDelivered()) {
                        $validator->errors()->add('parcel_ids', "Parcel {$parcel->tracking_no} is already delivered and cannot be reassigned.");
                    }
                    if (! $parcel->area_id) {
                        $validator->errors()->add('parcel_ids', "Parcel {$parcel->tracking_no} has no barangay assigned.");
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'rider_id.required' => 'A rider is required for bulk assignment.',
            'parcel_ids.required' => 'Please select at least one parcel to assign.',
            'parcel_ids.min' => 'Please select at least one parcel to assign.',
        ];
    }
}
