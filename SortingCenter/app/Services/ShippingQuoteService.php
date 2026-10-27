<?php

namespace App\Services;

use App\Models\Area;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use Illuminate\Validation\ValidationException;

class ShippingQuoteService
{
    /**
     * @return array{serviceable: bool, zone: ?string, rate: ?string, chargeable_weight: int, fee: float, currency: string, message: ?string}
     */
    public function quote(string $address, float $weightKg, ?string $region = null): array
    {
        if ($weightKg <= 0) {
            throw ValidationException::withMessages(['weight_kg' => 'Weight must be greater than zero.']);
        }

        $zone = $this->matchZone($address, $region);

        if (! $zone || ! $zone->is_serviceable) {
            return [
                'serviceable' => false,
                'zone' => $zone?->name,
                'rate' => null,
                'chargeable_weight' => ShippingRate::roundUpWeight($weightKg),
                'fee' => 0.0,
                'currency' => 'PHP',
                'message' => 'This area is not serviceable yet. Add a serviceable zone and rate to enable checkout.',
            ];
        }

        $rate = $this->activeRate($zone, $weightKg);

        if (! $rate) {
            return [
                'serviceable' => false,
                'zone' => $zone->name,
                'rate' => null,
                'chargeable_weight' => ShippingRate::roundUpWeight($weightKg),
                'fee' => 0.0,
                'currency' => 'PHP',
                'message' => 'No active shipping rate covers this weight for the matched zone.',
            ];
        }

        return [
            'serviceable' => true,
            'zone' => $zone->name,
            'rate' => $rate->name,
            'chargeable_weight' => ShippingRate::roundUpWeight($weightKg),
            'fee' => $rate->feeForWeight($weightKg),
            'currency' => 'PHP',
            'message' => null,
        ];
    }

    public function matchZone(string $address, ?string $region = null): ?ShippingZone
    {
        $needle = mb_strtolower(trim($address.' '.$region));
        $area = Area::matchForAddress($address);

        return ShippingZone::query()
            ->orderByDesc('is_serviceable')
            ->get()
            ->first(function (ShippingZone $zone) use ($needle, $area) {
                if ($area && in_array($area->code, $zone->areas ?? [], true)) {
                    return true;
                }

                $names = array_filter(array_merge([$zone->name, $zone->region], $zone->areas ?? []));

                foreach ($names as $name) {
                    if ($name !== '' && str_contains($needle, mb_strtolower((string) $name))) {
                        return true;
                    }
                }

                return false;
            });
    }

    public function activeRate(ShippingZone $zone, float $weightKg): ?ShippingRate
    {
        $kg = ShippingRate::roundUpWeight($weightKg);

        return $zone->rates()
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('effective_from')->orWhere('effective_from', '<=', now()))
            ->where('min_weight_kg', '<=', $kg)
            ->where(fn ($q) => $q->whereNull('max_weight_kg')->orWhere('max_weight_kg', '>=', $kg))
            ->orderBy('min_weight_kg')
            ->first();
    }
}
