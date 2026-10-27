<?php

namespace Tests\Feature;

use App\Models\HubProfile;
use Database\Seeders\SortingCenterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SortingCenterSeeder::class);
    }

    public function test_hub_defaults_to_the_single_configured_name(): void
    {
        $this->assertSame(
            config('sorting.hub_name'),
            HubProfile::current()->name
        );

        $this->get(route('account.index'))
            ->assertOk()
            ->assertSee(config('sorting.hub_name'));
    }

    public function test_account_requires_entity_name_and_returns_field_keyed_errors(): void
    {
        $this->postJson(route('account.update'), $this->payload([
            'entity_name' => '',
        ]))->assertStatus(422)->assertJsonValidationErrors(['entity_name']);
    }

    public function test_contact_must_be_at_least_eighteen(): void
    {
        $this->postJson(route('account.update'), $this->payload([
            'birthday' => now()->subYears(17)->toDateString(),
        ]))->assertStatus(422)->assertJsonValidationErrors(['birthday']);
    }

    public function test_phone_accepts_ph_mobile_and_landline_formats(): void
    {
        $this->postJson(route('account.update'), $this->payload([
            'contact_phone' => '0917 123 4567',
        ]))->assertOk()->assertJson(['ok' => true]);

        $this->assertSame('0917 123 4567', HubProfile::current()->contact_phone);

        $this->postJson(route('account.update'), $this->payload([
            'contact_phone' => '(049) 123-4567',
        ]))->assertOk()->assertJson(['ok' => true]);

        $this->assertSame('(049) 123-4567', HubProfile::current()->contact_phone);
    }

    public function test_phone_rejects_invalid_formats(): void
    {
        $this->postJson(route('account.update'), $this->payload([
            'contact_phone' => '12345',
        ]))->assertStatus(422)->assertJsonValidationErrors(['contact_phone']);
    }

    public function test_changing_entity_name_requires_confirmation_and_returns_409(): void
    {
        $this->postJson(route('account.update'), $this->payload([
            'entity_name' => 'New Hub Name',
        ]))->assertStatus(409)->assertJson([
            'ok' => false,
        ]);

        $this->assertNotSame('New Hub Name', HubProfile::current()->name);
    }

    public function test_confirmed_entity_name_change_updates_the_hub(): void
    {
        $this->postJson(route('account.update'), $this->payload([
            'entity_name' => 'New Hub Name',
            'confirm' => true,
        ]))->assertOk()->assertJson(['ok' => true]);

        $this->assertSame('New Hub Name', HubProfile::current()->name);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'entity_name' => HubProfile::current()->name,
            'first_name' => 'Ana',
            'last_name' => 'Reyes',
            'middle_initial' => 'B',
            'staff_title' => 'Operations Manager',
            'birthday' => now()->subYears(30)->toDateString(),
            'contact_email' => 'ana@santacruzhub.ph',
            'contact_phone' => '0917 123 4567',
            'address' => 'Santa Cruz, Laguna',
            'coverage_note' => 'Covers Santa Cruz barangays.',
        ], $overrides);
    }
}
