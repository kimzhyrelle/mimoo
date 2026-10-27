<?php

namespace App\Console\Commands;

use App\Services\BranchChecklistService;
use App\Services\ShiftSchedule;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('checklist:reset {--shift= : Shift key (morning, afternoon, night)} {--date= : Shift date (Y-m-d)}')]
#[Description('Open a fresh set of branch checklists for the active shift.')]
class ResetShiftChecklists extends Command
{
    public function handle(BranchChecklistService $checklists): int
    {
        $shiftKey = $this->option('shift');
        $date = $this->option('date');

        $shift = $shiftKey
            ? ShiftSchedule::for((string) $shiftKey, $date)
            : ShiftSchedule::current();

        $opened = $checklists->ensureShift($shift);

        $this->info('Checklists ready for '.$shift['label'].' on '.$shift['date']->toDateString().':');

        foreach ($opened as $key => $checklist) {
            $this->line(sprintf(
                '  - %s: %s (%d items%s)',
                $key,
                $checklist->title,
                $checklist->items->count(),
                $checklist->isSubmitted() ? ', already submitted' : ', open'
            ));
        }

        return self::SUCCESS;
    }
}
