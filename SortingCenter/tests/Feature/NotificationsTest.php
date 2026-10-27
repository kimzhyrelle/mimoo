<?php

namespace Tests\Feature;

use App\Models\Activity;
use Database\Seeders\SortingCenterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SortingCenterSeeder::class);
    }

    public function test_bell_returns_grouped_actionable_alerts_and_activity(): void
    {
        $response = $this->getJson(route('notifications.index'));

        $response->assertOk()->assertJsonStructure([
            'notifications' => [['type', 'title', 'description', 'url', 'created_at', 'read']],
            'unread_count',
            'actionable_count',
        ]);

        $types = collect($response->json('notifications'))->pluck('type')->unique();
        $this->assertTrue($types->contains('overdue'));
        $this->assertTrue($types->contains('unassigned'));
        $this->assertTrue($types->contains('kyc'));
        $this->assertGreaterThan(0, $response->json('unread_count'));
    }

    public function test_mark_all_read_clears_activity_unread_but_keeps_actionable_alerts(): void
    {
        $actionable = $this->getJson(route('notifications.index'))->json('actionable_count');
        $this->assertGreaterThan(0, Activity::unread()->count());

        $response = $this->postJson(route('notifications.mark-all-read'));

        $response->assertOk()->assertJson(['ok' => true]);
        $this->assertSame(0, Activity::unread()->count());
        $this->assertSame($actionable, $response->json('unread_count'));
    }

    public function test_a_single_activity_can_be_marked_read(): void
    {
        $activity = Activity::query()->whereNull('read_at')->firstOrFail();

        $this->postJson(route('notifications.mark-read', $activity))
            ->assertOk()
            ->assertJson(['ok' => true, 'message' => 'Notification marked as read.']);

        $this->assertNotNull($activity->fresh()->read_at);
    }
}
