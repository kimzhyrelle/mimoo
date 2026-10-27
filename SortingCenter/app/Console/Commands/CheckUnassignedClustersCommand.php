<?php

namespace App\Console\Commands;

use App\Services\AssignmentService;
use Illuminate\Console\Command;

class CheckUnassignedClustersCommand extends Command
{
    protected $signature = 'sorting:check-unassigned-clusters';

    protected $description = 'Detect clusters/routes with parcels or cutoffs but no assigned active courier';

    public function handle(AssignmentService $assignmentService): int
    {
        $unassigned = $assignmentService->getUnassignedClusters();

        if ($unassigned->isEmpty()) {
            $this->info('All active clusters have couriers assigned.');

            return self::SUCCESS;
        }

        $this->warn("Found {$unassigned->count()} unassigned cluster(s) with parcels in staging:");

        $rows = $unassigned->map(fn ($area) => [
            'ID' => $area->id,
            'Code' => $area->code,
            'Name' => $area->name,
            'Cutoff' => $area->cutoff_time ?? '—',
            'Pending Parcels' => $area->parcels()->whereIn('status', ['sorted', 'assigned'])->count(),
        ]);

        $this->table(['ID', 'Code', 'Name', 'Cutoff', 'Pending Parcels'], $rows);

        return self::SUCCESS;
    }
}
