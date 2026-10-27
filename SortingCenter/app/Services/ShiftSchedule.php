<?php

namespace App\Services;

use Illuminate\Support\Carbon;

class ShiftSchedule
{
    /**
     * Resolve the active shift for a timestamp.
     *
     * @return array{key: string, label: string, date: Carbon, start: Carbon, end: Carbon}
     */
    public static function current(?Carbon $at = null): array
    {
        $now = $at ? $at->copy() : now();
        $shifts = config('sorting.shifts', []);

        foreach ($shifts as $key => $shift) {
            $start = $now->copy()->setTimeFromTimeString($shift['start']);
            $end = $now->copy()->setTimeFromTimeString($shift['end']);
            $overnight = $end->lessThanOrEqualTo($start);

            $active = $overnight
                ? ($now->greaterThanOrEqualTo($start) || $now->lessThan($end))
                : ($now->greaterThanOrEqualTo($start) && $now->lessThan($end));

            if (! $active) {
                continue;
            }

            $shiftDate = $overnight && $now->lessThan($end)
                ? $now->copy()->subDay()->startOfDay()
                : $now->copy()->startOfDay();

            return self::make($key, $shift, $shiftDate);
        }

        $key = array_key_first($shifts) ?: 'morning';
        $shift = $shifts[$key] ?? ['label' => 'Morning', 'start' => '06:00', 'end' => '14:00'];

        return self::make($key, $shift, $now->copy()->startOfDay());
    }

    /**
     * Resolve an explicit shift key/date pair, useful for the scheduled reset.
     *
     * @return array{key: string, label: string, date: Carbon, start: Carbon, end: Carbon}
     */
    public static function for(string $key, ?string $date = null): array
    {
        $shifts = config('sorting.shifts', []);
        $shift = $shifts[$key] ?? $shifts['morning'] ?? ['label' => 'Morning', 'start' => '06:00', 'end' => '14:00'];
        $day = $date ? Carbon::parse($date)->startOfDay() : now()->startOfDay();

        return self::make(array_key_exists($key, $shifts) ? $key : 'morning', $shift, $day);
    }

    /**
     * @param  array{label: string, start: string, end: string}  $shift
     * @return array{key: string, label: string, date: Carbon, start: Carbon, end: Carbon}
     */
    private static function make(string $key, array $shift, Carbon $date): array
    {
        return [
            'key' => $key,
            'label' => $shift['label'],
            'date' => $date->copy()->startOfDay(),
            'start' => $date->copy()->setTimeFromTimeString($shift['start']),
            'end' => $date->copy()->setTimeFromTimeString($shift['end']),
        ];
    }
}
