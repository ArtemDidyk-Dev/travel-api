<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\Travel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToursListTest extends TestCase
{
    use RefreshDatabase;


    public function test_tours_list_by_travel_slug_returns_correct_tours(): void
    {
        $travel = Travel::factory()->create();
        $tour = Tour::factory()->create(['travel_id' => $travel->id]);
        $response = $this->get(route('tours.index', $travel));
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $tour->id]);
    }

    public function test_tours_list_returns_paginated_data_correctly(): void
    {
        $travel = Travel::factory()->create();
        Tour::factory(16)->create(['travel_id' => $travel->id]);
        $response = $this->get(route('tours.index', $travel));
        $response->assertOk();
        $response->assertJsonCount(15, 'data');
        $response->assertJsonPath('meta.last_page', 2);

    }

    public function test_tours_price_is_show_correctly(): void
    {
        $travel = Travel::factory()->create();
        Tour::factory(16)->create(
            [
                'travel_id' => $travel->id,
                'price' => 11.33,
            ]
        );
        $response = $this->get(route('tours.index', $travel));
        $response->assertOk();
        $response->assertJsonFragment(['price' => '11.33']);
    }

    public function test_tours_returns_404_for_nonexistent_travel(): void
    {
        $nonExistentTravelSlug = 'test-test';
        $response = $this->getJson(route('tours.index', ['travel' => $nonExistentTravelSlug]));
        $response->assertNotFound();
    }

    public function test_tours_list_returns_pagination(): void
    {
        $travel = Travel::factory()->create();
        $response = $this->get(route('tours.index', $travel));
        $response->assertOk();
        $response->assertJsonCount(15, 'data');
        $response->assertJsonPath('meta.per_page', 15);
        $response->assertJsonPath('meta.total', 16);
        $response->assertJsonPath('meta.current_page', 1);

    }
}
