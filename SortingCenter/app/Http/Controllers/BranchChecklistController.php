<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Branch;
use App\Models\HubProfile;
use App\Services\BranchChecklistService;
use App\Services\ShiftSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BranchChecklistController extends Controller
{
    public function __construct(private BranchChecklistService $checklists) {}

    public function index(Request $request)
    {
        $hub = HubProfile::current();
        $shift = ShiftSchedule::current();
        $definitions = config('sorting.branch_checklists', []);

        $checklists = [];

        foreach ($definitions as $key => $definition) {
            $checklist = $this->checklists->ensure($key, $definition, $shift);

            $checklists[$key] = [
                'title' => $checklist->title,
                'station' => $checklist->station,
                'shift' => $checklist->shift_label,
                'submitted' => $checklist->isSubmitted(),
                'submitted_at' => $checklist->submitted_at,
                'submitted_by' => $checklist->submitted_by,
                'items' => $checklist->items->map(fn ($item) => [
                    'key' => $item->key,
                    'title' => $item->title,
                    'body' => $item->body,
                    'done' => $item->checked,
                    'note' => $item->note,
                    'photo_path' => $item->photo_path,
                    'badge' => $item->checked ? 'Verified' : 'Action Needed',
                ])->values()->all(),
            ];
        }

        $search = trim((string) $request->query('q', ''));

        $branches = Branch::orderByRaw("FIELD(type, 'branch', 'hub', 'warehouse')")
            ->orderBy('code')
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.addcslashes($search, '\\%_').'%';
                $query->where(fn ($query) => $query
                    ->where('code', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhere('address', 'like', $like));
            })
            ->get()
            ->map(function (Branch $branch) {
                $tier = match ($branch->type) {
                    'branch' => 1,
                    'hub' => 2,
                    default => 3,
                };

                return [
                    'tier' => $tier,
                    'label' => $branch->typeLabel(),
                    'code' => $branch->code,
                    'name' => $branch->name,
                    'sub' => ($branch->address ?: 'No address on file').' - '.$branch->code,
                    'capacity' => match ($branch->type) {
                        'branch' => '20,000',
                        'hub' => '45,000',
                        default => '50,000',
                    },
                    'status' => $branch->is_active ? 'Active' : 'Offline',
                    'icon' => match ($branch->type) {
                        'branch' => 'network',
                        'hub' => 'building',
                        default => 'box',
                    },
                ];
            })
            ->values()
            ->all();

        return view('sorting.branches', [
            'hub' => $hub,
            'branches' => $branches,
            'checklists' => $checklists,
            'shift' => $shift,
            'supervisor' => [
                'name' => $hub->staff_name ?: 'Sorting Staff',
                'title' => $hub->staff_title ?: 'Lead Floor Supervisor',
                'initials' => $hub->display_initials ?? 'SC',
            ],
        ]);
    }

    public function submit(Request $request): JsonResponse
    {
        $request->merge(['items' => $this->decodeItems($request->input('items', []))]);

        $data = $request->validate([
            'list' => ['required', Rule::in(array_keys(config('sorting.branch_checklists', [])))],
            'items' => ['nullable', 'array'],
            'items.*.key' => ['required', 'string', 'max:50'],
            'items.*.checked' => ['sometimes', 'boolean'],
            'items.*.note' => ['nullable', 'string', 'max:200'],
            'items.*.photo_path' => ['nullable', 'string', 'max:255'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['nullable', 'image', 'max:5120'],
        ]);

        $definition = config('sorting.branch_checklists.'.$data['list']);

        if (! is_array($definition)) {
            return response()->json(['ok' => false, 'message' => 'Unknown checklist.'], 422);
        }

        $shift = ShiftSchedule::current();
        $checklist = $this->checklists->ensure($data['list'], $definition, $shift);

        if ($checklist->isLocked()) {
            return response()->json([
                'ok' => false,
                'message' => 'This checklist was already submitted for the '.$checklist->shift_label.' shift.',
                'submitted_at' => $checklist->submitted_at?->toIso8601String(),
                'submitted_by' => $checklist->submitted_by,
            ], 409);
        }

        $items = collect($data['items'] ?? [])->keyBy('key');
        $photos = $request->file('photos', []);
        $uploaded = [];

        DB::transaction(function () use ($checklist, $items, $photos, $definition, &$uploaded) {
            foreach ($checklist->items as $item) {
                $incoming = $items->get($item->key, []);
                $validKeys = collect($definition['items'] ?? [])->pluck('key')->all();

                if (! in_array($item->key, $validKeys, true)) {
                    continue;
                }

                $item->checked = (bool) ($incoming['checked'] ?? false);
                $item->note = filled($incoming['note'] ?? null) ? trim((string) $incoming['note']) : null;
                $item->photo_path = $incoming['photo_path'] ?? null;

                if (isset($photos[$item->key])) {
                    $path = $photos[$item->key]->store(
                        'checklists/'.$checklist->branch_key.'/'.$checklist->shift_date->toDateString().'/'.$checklist->shift_key,
                        'public'
                    );
                    $item->photo_path = $path;
                    $uploaded[$item->key] = $path;
                }

                $item->save();
            }

            $hub = HubProfile::current();
            $checklist->update([
                'submitted_at' => now(),
                'submitted_by' => $hub->staff_name ?: 'Sorting Staff',
            ]);
        });

        $checklist->refresh()->load('items');
        $completed = $checklist->items->where('checked', true)->count();
        $total = $checklist->items->count();

        Activity::create([
            'icon' => $completed === $total ? 'green' : 'accent',
            'description' => 'Shift checklist submitted - <b>'.ucfirst($data['list']).'</b> '.$completed.' of '.$total.' steps completed',
        ]);

        return response()->json([
            'ok' => true,
            'message' => ucfirst($data['list']).' checklist submitted to the regional ledger.',
            'submitted_at' => $checklist->submitted_at?->toIso8601String(),
            'submitted_by' => $checklist->submitted_by,
            'completed' => $completed,
            'total' => $total,
            'photos' => $uploaded,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function decodeItems(mixed $items): array
    {
        if (is_string($items)) {
            $decoded = json_decode($items, true);

            return is_array($decoded) ? $decoded : [];
        }

        return is_array($items) ? $items : [];
    }
}
