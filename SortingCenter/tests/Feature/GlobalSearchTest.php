<?php

namespace Tests\Feature;

use Database\Seeders\SortingCenterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SortingCenterSeeder::class);
    }

    public function test_search_returns_grouped_results_with_urls(): void
    {
        $response = $this->getJson(route('search', ['q' => '#1004']));

        $response->assertOk()
            ->assertJsonStructure([
                'query',
                'parcels' => [['id', 'tracking_no', 'title', 'subtitle', 'status', 'url']],
                'riders',
                'sellers',
            ])
            ->assertJsonPath('parcels.0.tracking_no', '#1004')
            ->assertJsonPath('query', '#1004');
    }

    public function test_search_finds_riders_and_sellers(): void
    {
        $riders = $this->getJson(route('search', ['q' => 'J. Ramos']))->json('riders');
        $this->assertNotEmpty($riders);
        $this->assertSame('J. Ramos', $riders[0]['title']);

        $sellers = $this->getJson(route('search', ['q' => 'Rosales']))->json('sellers');
        $this->assertNotEmpty($sellers);
        $this->assertSame('Rosales Dry Goods', $sellers[0]['title']);
    }

    public function test_search_limits_each_group_to_five_rows(): void
    {
        $parcels = $this->getJson(route('search', ['q' => '#10']))->json('parcels');

        $this->assertLessThanOrEqual(5, count($parcels));
        $this->assertGreaterThan(0, count($parcels));
    }

    public function test_blank_search_returns_empty_groups(): void
    {
        $this->getJson(route('search', ['q' => '']))
            ->assertOk()
            ->assertJson([
                'query' => '',
                'parcels' => [],
                'riders' => [],
                'sellers' => [],
            ]);
    }
}
