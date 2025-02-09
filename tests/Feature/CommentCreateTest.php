<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enum\ImagePath;
use App\Enum\Role as RoleEnum;
use App\Models\Image;
use App\Models\Role;
use App\Models\Tour;
use App\Models\Travel;
use App\Models\User;
use App\Services\ImageInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommentCreateTest extends TestCase
{
    use RefreshDatabase;

    private ImageInterface $imageService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->imageService = app(ImageInterface::class);
    }

    #[Test]
    public function it_user_can_create(): void
    {
        $this->createUser();
        $travel = Travel::factory()->create([
            'is_public' => true,
        ]);
        $tour = Tour::factory()->create([
            'travel_id' => $travel->id,
        ]);
        $imageFirst = UploadedFile::fake()->image('tour-image.jpg');
        $imageSecond = UploadedFile::fake()->image('tour-image.jpg');
        $response = $this->post(route('travels.tour.comments.store', [
            'travel' => $travel,
            'tour' => $tour,
        ]), [
            'text' => 'Hello',
            'images' => [$imageFirst, $imageSecond],
        ]);
        foreach ([$imageFirst, $imageSecond] as $image) {
            Storage::disk('public')->assertExists($this->imageService->processFile($image, ImagePath::TOUR_PATH));
        }
        $response->assertStatus(201);
        $response->assertJsonFragment([
            'message' => 'Comment added, stay tuned for updates',
        ]);
        $this->assertDatabaseCount('comments', 1);
        $this->assertDatabaseCount(Image::getTableName(), 2);
    }

    #[Test]
    public function it_invalid_data_save(): void
    {
        $this->createUser();
        $travel = Travel::factory()->create([
            'is_public' => true,
        ]);
        $tour = Tour::factory()->create([
            'travel_id' => $travel->id,
        ]);
        $response = $this->post(route('travels.tour.comments.store', [
            'travel' => $travel,
            'tour' => $tour,
        ]));
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'text' => 'The text field is required.',
        ]);
    }

    #[Test]
    public function it_unauthorized_user(): void
    {
        $travel = Travel::factory()->create([
            'is_public' => true,
        ]);
        $tour = Tour::factory()->create([
            'travel_id' => $travel->id,
        ]);

        $response = $this->postJson(route('travels.tour.comments.store', [
            'travel' => $travel,
            'tour' => $tour,
        ]), [
            'text' => 'Hello',
        ]);
        $response->assertStatus(401);
    }

    private function createUser(): User
    {
        $roleUser = $this->createRole(RoleEnum::USER->name);

        $user = User::factory()->create([
            'name' => 'user',
            'email' => 'user@user.com',
            'password' => Hash::make('password'),
        ]);

        $user->roles()
            ->attach($roleUser);
        Sanctum::actingAs($user);

        return $user;
    }

    private function createRole(string $roleName): Role
    {
        return Role::firstOrCreate([
            'name' => $roleName,
        ]);
    }
}
