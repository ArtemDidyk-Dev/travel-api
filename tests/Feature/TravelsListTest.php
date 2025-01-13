<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Travel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TravelsListTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_paginated_travels_list_correctly(): void
    {
        Travel::factory(16)->create([
            'is_public' => true,
        ]);

        $response = $this->get(route('travels.index'));

        $response->assertOk()
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.last_page', 2);
    }

    #[Test]
    public function it_shows_only_public_travels_in_list(): void
    {
        $publicTravel = Travel::factory()->create([
            'is_public' => true,
        ]);
        $privateTravel = Travel::factory()->create([
            'is_public' => false,
        ]);

        $response = $this->get(route('travels.index'));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', $publicTravel->name)
            ->assertJsonMissing([
                'name' => $privateTravel->name,
            ]);
    }

    #[Test]
    public function it_returns_404_for_non_existent_travel(): void
    {
        $nonExistentTravelSlug = 'non-existent-travel';

        $response = $this->getJson(route('travels.show', $nonExistentTravelSlug));

        $response->assertNotFound();
    }
}
