<?php

namespace Tests\Feature;

use App\Models\Travel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelsListTest extends TestCase
{
    use RefreshDatabase;
    public function test_travels_list_returns_paginated_data_correctly(): void
    {
        Travel::factory(16)->create(['is_public' => true]);
        $response = $this->get(route('travels.index'));
        $response->assertOk();
        $response->assertJsonCount(15, 'data');
        $response->assertJsonPath('meta.last_page', 2);

    }

    public function test_travels_list_shows_only_public_travels(): void
    {
        $public = Travel::factory()->create(['is_public' => true]);
        $response = $this->get(route('travels.index'));
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name',  $public->name);
    }

    public function test_travels_returns_404(): void
    {
        $nonExistentTravelSlug = 'test-test';
        $response = $this->getJson(route('travels.show', $nonExistentTravelSlug));
        $response->assertNotFound();
    }


}
