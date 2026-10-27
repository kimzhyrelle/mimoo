<?php

namespace Tests\Feature;

use App\Models\BranchChecklist;
use Database\Seeders\SortingCenterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BranchChecklistTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SortingCenterSeeder::class);
    }

    public function test_branches_page_renders_the_persisted_checklists(): void
    {
        $this->get(route('branches.index'))
            ->assertOk()
            ->assertSee('Inbound Manifest Triage')
            ->assertSee('Outbound Dispatch Triage');

        $this->assertDatabaseCount('branch_checklists', 2);
        $this->assertDatabaseCount('branch_checklist_items', 6);
    }

    public function test_submitting_locks_the_checklist_and_stores_notes_and_photos(): void
    {
        Storage::fake('public');

        $items = [
            ['key' => 'truck_seal', 'checked' => true, 'note' => 'Seal matched'],
            ['key' => 'qr_scan', 'checked' => true, 'note' => 'All 42 parcels scanned'],
            ['key' => 'damage_weight', 'checked' => false, 'note' => 'One crushed box logged'],
        ];

        $response = $this->post(route('branches.checklist.submit'), [
            'list' => 'inbound',
            'items' => json_encode($items),
            'photos' => ['truck_seal' => UploadedFile::fake()->createWithContent(
                'seal.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
            )],
        ], ['Accept' => 'application/json']);

        $response->assertOk()->assertJson(['ok' => true, 'completed' => 2, 'total' => 3]);

        $checklist = BranchChecklist::query()->where('branch_key', 'inbound')->firstOrFail();
        $this->assertNotNull($checklist->submitted_at);
        $this->assertSame('Sorting Staff', $checklist->submitted_by);

        $this->assertDatabaseHas('branch_checklist_items', [
            'branch_checklist_id' => $checklist->id,
            'key' => 'truck_seal',
            'checked' => true,
            'note' => 'Seal matched',
        ]);

        $photo = $checklist->items()->where('key', 'truck_seal')->firstOrFail()->photo_path;
        $this->assertNotNull($photo);
        Storage::disk('public')->assertExists($photo);
    }

    public function test_a_submitted_checklist_cannot_be_resubmitted(): void
    {
        $this->postJson(route('branches.checklist.submit'), [
            'list' => 'inbound',
            'items' => [['key' => 'truck_seal', 'checked' => true]],
        ])->assertOk();

        $this->postJson(route('branches.checklist.submit'), [
            'list' => 'inbound',
            'items' => [['key' => 'truck_seal', 'checked' => false]],
        ])->assertStatus(409)->assertJson(['ok' => false]);

        $this->assertDatabaseHas('branch_checklist_items', [
            'key' => 'truck_seal',
            'checked' => true,
        ]);
    }

    public function test_the_reset_command_opens_a_fresh_list_for_the_next_shift(): void
    {
        $this->travelTo(Carbon::parse('2026-05-01 10:00:00'));

        $this->artisan('checklist:reset')->assertSuccessful();

        $this->assertDatabaseHas('branch_checklists', [
            'branch_key' => 'inbound',
            'shift_key' => 'morning',
            'shift_date' => '2026-05-01',
        ]);

        $this->travelTo(Carbon::parse('2026-05-01 15:00:00'));
        $this->artisan('checklist:reset')->assertSuccessful();

        $this->assertDatabaseHas('branch_checklists', [
            'branch_key' => 'inbound',
            'shift_key' => 'afternoon',
            'shift_date' => '2026-05-01',
        ]);
    }
}
