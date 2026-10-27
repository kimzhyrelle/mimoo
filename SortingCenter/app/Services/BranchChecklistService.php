<?php

namespace App\Services;

use App\Models\BranchChecklist;
use Illuminate\Support\Carbon;

class BranchChecklistService
{
    /**
     * Create the checklist and its configured items for a shift if missing.
     *
     * @param  array{title: string, station?: string, items: array<int, array{key: string, title: string, body?: string}>}  $definition
     * @param  array{key: string, label: string, date: Carbon}  $shift
     */
    public function ensure(string $key, array $definition, array $shift): BranchChecklist
    {
        $checklist = BranchChecklist::firstOrCreate(
            [
                'branch_key' => $key,
                'shift_key' => $shift['key'],
                'shift_date' => $shift['date']->toDateString(),
            ],
            [
                'title' => $definition['title'],
                'station' => $definition['station'] ?? null,
                'shift_label' => $shift['label'],
            ]
        );

        foreach ($definition['items'] ?? [] as $item) {
            $checklist->items()->firstOrCreate(
                ['key' => $item['key']],
                ['title' => $item['title'], 'body' => $item['body'] ?? null]
            );
        }

        return $checklist->load('items');
    }

    /**
     * Ensure every configured checklist exists for the given shift.
     *
     * @param  array{key: string, label: string, date: Carbon}  $shift
     * @return array<string, BranchChecklist>
     */
    public function ensureShift(array $shift): array
    {
        $created = [];

        foreach (config('sorting.branch_checklists', []) as $key => $definition) {
            $created[$key] = $this->ensure($key, $definition, $shift);
        }

        return $created;
    }
}
