<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use Database\Seeders\SortingCenterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessagingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SortingCenterSeeder::class);
    }

    public function test_messages_page_lists_conversations_and_unread_counts_per_tab(): void
    {
        Message::query()->update(['read_at' => now()]);

        $seller = Conversation::query()->where('name', 'Rosales Dry Goods')->firstOrFail();
        Message::query()->where('conversation_id', $seller->id)->update(['read_at' => null]);

        $admin = Conversation::query()->where('type', 'admin')->firstOrFail();
        Message::query()->where('conversation_id', $admin->id)->update(['read_at' => null]);

        $response = $this->get(route('chat.index'));

        $response->assertOk()->assertSee('Rosales Dry Goods');

        $response->assertViewHas('tabCounts', function (array $counts) {
            return $counts['all'] === 4
                && $counts['seller'] === 3
                && $counts['admin'] === 1
                && $counts['rider'] === 0;
        });

        $this->assertSame(3, $seller->unreadCount());
    }

    public function test_sending_a_message_persists_it_and_links_the_parcel(): void
    {
        $conversation = Conversation::query()->where('type', 'rider')->firstOrFail();

        $this->postJson(route('chat.messages.store', $conversation), [
            'body' => 'The rider is on the way.',
        ])->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender' => 'me',
            'body' => 'The rider is on the way.',
            'parcel_id' => $conversation->parcel_id,
        ]);

        $this->assertNotNull($conversation->fresh()->last_message_at);
    }

    public function test_opening_a_conversation_clears_its_unread_messages(): void
    {
        $conversation = Conversation::query()->where('type', 'seller')->firstOrFail();
        Message::query()->where('conversation_id', $conversation->id)->update(['read_at' => null]);

        $this->assertGreaterThan(0, $conversation->unreadCount());

        $this->postJson(route('chat.read', $conversation))
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertSame(0, $conversation->unreadCount());
    }
}
