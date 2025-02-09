<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Tour;
use App\Models\Travel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ToursListTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_correct_tours_for_travel_slug(): void
    {
        $travel = Travel::factory()->create([
            'is_public' => true,
        ]);
        $tour = Tour::factory()->create([
            'travel_id' => $travel->id,
        ]);

        $response = $this->getJson(route('tours.index', $travel));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'id' => $tour->id,
            ]);
    }

    #[Test]
    public function it_returns_correct_tour_for_id(): void
    {
        $travel = Travel::factory()->create([
            'is_public' => true,
        ]);
        $tour = Tour::factory()->create([
            'travel_id' => $travel->id,
        ]);

        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'tour_id' => $tour->id,
            'is_public' => true,
        ]);

        $response = $this->getJson(route('tours.show', [
            'travel' => $travel,
            'tour' => $tour,
        ]));
        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $tour->id,
            'images' => [],
            'comments' => [
                [
                    'id' => $comment->id,
                    'created_at' => $comment->created_at->format('Y M d'),
                    'text' => $comment->text,
                    'images' => [],
                    'user' => $user->name,
                ],
            ],
        ]);
    }

    #[Test]
    public function it_returns_paginated_tours_list(): void
    {
        $travel = Travel::factory()->create([
            'is_public' => true,
        ]);
        Tour::factory(16)->create([
            'travel_id' => $travel->id,
        ]);

        $response = $this->getJson(route('tours.index', $travel));

        $response->assertOk()
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.last_page', 2);
    }

    #[Test]
    public function it_displays_correct_tour_price(): void
    {
        $travel = Travel::factory()->create([
            'is_public' => true,
        ]);
        Tour::factory(16)->create([
            'travel_id' => $travel->id,
            'price' => 11.33,
        ]);

        $response = $this->getJson(route('tours.index', $travel));

        $response->assertOk()
            ->assertJsonFragment([
                'price' => '11.33',
            ]);
    }

    #[Test]
    public function it_returns_404_for_nonexistent_travel(): void
    {
        $nonExistentTravelSlug = 'non-existent-slug';

        $response = $this->getJson(route('tours.index', [
            'travel' => $nonExistentTravelSlug,
        ]));

        $response->assertNotFound();
    }

    #[Test]
    public function it_paginates_tours_list_correctly(): void
    {
        $travel = Travel::factory()->create([
            'is_public' => true,
        ]);
        Tour::factory(16)->create([
            'travel_id' => $travel->id,
        ]);

        $response = $this->getJson(route('tours.index', $travel));

        $response->assertOk()
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonPath('meta.total', 16)
            ->assertJsonPath('meta.current_page', 1);
    }

    #[Test]
    public function it_sorts_tours_by_start_date(): void
    {
        $travel = Travel::factory()->create([
            'is_public' => true,
        ]);
        $earlierTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'start_date' => now(),
            'end_date' => now()
                ->addDays(2),
        ]);
        $laterTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'start_date' => now()
                ->addDays(2),
            'end_date' => now()
                ->addDays(3),
        ]);

        $response = $this->getJson(route('tours.index', $travel));

        $response->assertOk()
            ->assertJsonPath('data.0.id', $earlierTour->id)
            ->assertJsonPath('data.1.id', $laterTour->id);
    }

    #[Test]
    public function it_sorts_tours_by_price(): void
    {
        $travel = Travel::factory()->create([
            'is_public' => true,
        ]);
        $expensiveTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 200,
        ]);
        $cheapEarlierTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 100,
            'start_date' => now(),
            'end_date' => now()
                ->addDays(2),
        ]);
        $cheapLaterTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 100,
            'start_date' => now()
                ->addDays(2),
            'end_date' => now()
                ->addDays(3),
        ]);

        $response = $this->getJson(route('tours.index', [
            'travel' => $travel,
            'sort_by' => 'price',
            'sort_order' => 'asc',
        ]));

        $response->assertOk()
            ->assertJsonPath('data.0.id', $cheapEarlierTour->id)
            ->assertJsonPath('data.1.id', $cheapLaterTour->id)
            ->assertJsonPath('data.2.id', $expensiveTour->id);
    }

    #[Test]
    public function it_filters_tours_by_price_range(): void
    {
        $travel = Travel::factory()->create([
            'is_public' => true,
        ]);
        $expensiveTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 300,
        ]);
        $cheapTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 150,
        ]);

        $response = $this->getJson(route('tours.index', [
            'travel' => $travel,
            'price_from' => 150,
            'price_to' => 300,
            'sort_by' => 'price',
        ]));

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $cheapTour->id)
            ->assertJsonPath('data.1.id', $expensiveTour->id);

        $response = $this->getJson(route('tours.index', [
            'travel' => $travel,
            'price_from' => 200,
            'sort_by' => 'price',
        ]));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'id' => $expensiveTour->id,
            ])
            ->assertJsonMissing([
                'id' => $cheapTour->id,
            ]);
    }

    #[Test]
    public function it_filters_tours_by_data_range(): void
    {
        $travel = Travel::factory()->create([
            'is_public' => true,
        ]);
        $lateTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 300,
            'start_date' => '2024-12-30',
            'end_date' => '2025-1-12',
        ]);
        $earlierTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 300,
            'start_date' => '2024-12-1',
            'end_date' => '2025-1-1',
        ]);
        $response = $this->getJson(route('tours.index', [
            'travel' => $travel,
            'start_date' => '2024-12-29',
        ]));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'id' => $lateTour->id,
            ])
            ->assertJsonMissing([
                'id' => $earlierTour->id,
            ]);

        $response = $this->getJson(route('tours.index', [
            'travel' => $travel,
            'end_date' => '2025-1-1',
        ]));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'id' => $earlierTour->id,
            ])
            ->assertJsonMissing([
                'id' => $lateTour->id,
            ]);

        $includedTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 400,
            'start_date' => '2024-12-20',
            'end_date' => '2024-12-25',
        ]);
        $excludedTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 300,
            'start_date' => '2024-12-30',
            'end_date' => '2025-01-10',
        ]);

        $response = $this->getJson(route('tours.index', [
            'travel' => $travel,
            'start_date' => '2024-12-15',
            'end_date' => '2024-12-26',
        ]));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'id' => $includedTour->id,
            ])
            ->assertJsonMissing([
                'id' => $excludedTour->id,
            ]);

        $tour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 500,
            'start_date' => '2024-12-10',
            'end_date' => '2024-12-20',
        ]);

        $response = $this->getJson(route('tours.index', [
            'travel' => $travel,
            'start_date' => '2024-12-25',
            'end_date' => '2024-12-30',
        ]));

        $response->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonMissing([
                'id' => $tour->id,
            ]);
    }

    #[Test]
    public function it_returns_validation_errors_for_invalid_parameters(): void
    {
        $travel = Travel::factory()->create([
            'is_public' => true,
        ]);

        $response = $this->getJson(route('tours.index', [
            'travel' => $travel,
            'sort_by' => 'invalid',
        ]));
        $response->assertStatus(400);

        $response = $this->getJson(route('tours.index', [
            'travel' => $travel,
            'price_from' => 'invalid',
            'sort_by' => 'price',
        ]));
        $response->assertStatus(400);

        $response = $this->getJson(route('tours.index', [
            'travel' => $travel,
            'sort_order' => 'invalid',
        ]));
        $response->assertStatus(400);
    }
}
