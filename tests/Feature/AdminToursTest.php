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

class AdminToursTest extends TestCase
{
    use RefreshDatabase;

    private ImageInterface $imageService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->imageService = app(ImageInterface::class);
    }

    #[Test]
    public function it_admin_create_tour(): void
    {
        $this->createAdmin();
        $travel = Travel::factory()->create();
        $response = $this->postJson(route('admin.tours.store', $travel->id), $this->getTour());
        $response->assertCreated();
        $response->assertJsonFragment([
            'name' => $this->getTour()['name'],
        ]);
    }

    #[Test]
    public function it_editor_create_tour(): void
    {
        $this->createEditor();
        $travel = Travel::factory()->create();
        $response = $this->postJson(route('admin.tours.store', $travel->id), $this->getTour());
        $response->assertCreated();
        $response->assertJsonFragment([
            'name' => $this->getTour()['name'],
        ]);
    }

    #[Test]
    public function it_admin_update_tour(): void
    {
        $this->createAdmin();
        $travel = Travel::factory()->create();
        $tour = Tour::factory()->create([
            'travel_id' => $travel->id,
        ]);
        $response = $this->putJson(
            route('admin.tours.update', [
                'travel' => $travel,
                'tour' => $tour,
            ]),
            $this->getTour()
        );
        $response->assertOk();
        $response->assertJsonMissing([
            'name' => $tour->name,
        ]);
        $response->assertJsonFragment([
            'name' => $this->getTour()['name'],
        ]);
    }

    #[Test]
    public function it_editor_update_tour(): void
    {
        $this->createEditor();
        $travel = Travel::factory()->create();
        $tour = Tour::factory()->create([
            'travel_id' => $travel->id,
        ]);
        $response = $this->putJson(
            route('admin.tours.update', [
                'travel' => $travel,
                'tour' => $tour,
            ]),
            $this->getTour()
        );
        $response->assertOk();
        $response->assertJsonMissing([
            'name' => $tour->name,
        ]);
        $response->assertJsonFragment([
            'name' => $this->getTour()['name'],
        ]);
    }

    #[Test]
    public function it_admin_destroy_tour(): void
    {
        $this->createAdmin();
        $travel = Travel::factory()->create();
        $tour = Tour::factory()->create([
            'travel_id' => $travel->id,
        ]);
        $this->deleteJson(route('admin.tours.destroy', [
            'travel' => $travel,
            'tour' => $tour,
        ]))->assertStatus(204);
        $this->assertModelMissing($tour);
    }

    #[Test]
    public function it_editor_destroy_tour(): void
    {
        $this->createEditor();

        $travel = Travel::factory()->create();
        $tour = Tour::factory()->create([
            'travel_id' => $travel->id,
        ]);
        $this->deleteJson(route('admin.tours.destroy', [
            'travel' => $travel,
            'tour' => $tour,
        ]))->assertStatus(204);
        $this->assertModelMissing($tour);
    }

    #[Test]
    public function it_public_user_cannot_access_cud_tour(): void
    {
        $travel = Travel::factory()->create([
            'is_public' => true,
        ]);
        $response = $this->postJson(route('admin.tours.store', $travel), $this->getTour());
        $response->assertStatus(401);

        $tour = Tour::factory()->create([
            'travel_id' => $travel->id,
        ]);
        $response = $this->putJson(
            route('admin.tours.update', [
                'travel' => $travel,
                'tour' => $tour,
            ]),
            $this->getTour()
        );
        $response->assertStatus(401);
        $this->deleteJson(route('admin.tours.destroy', [
            'travel' => $travel,
            'tour' => $tour,
        ]))->assertStatus(401);
    }

    #[Test]
    public function it_invalid_data_save_for_tour(): void
    {
        $this->createAdmin();
        $travel = Travel::factory()->create();
        $response = $this->postJson(route('admin.tours.store', [
            'travel' => $travel,
        ]), [
            'name' => 'travel',
        ]);
        $response->assertStatus(422);
    }

    #[Test]
    public function it_can_upload_a_images_for_tour(): void
    {
        $this->createAdmin();
        $travel = Travel::factory()->create();
        $imageFirst = UploadedFile::fake()->image('tour-image.jpg');
        $imageSecond = UploadedFile::fake()->image('tour-image.jpg');
        $response = $this->post(
            route('admin.tours.store', $travel->id),
            $this->getTour([$imageFirst, $imageSecond])
        );
        foreach ([$imageFirst, $imageSecond] as $image) {
            Storage::disk('public')->assertExists($this->imageService->processFile($image, ImagePath::TOUR_PATH));
        }
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'start_date',
                'end_date',
                'price',
                'images' => [
                    '*' => ['id', 'url'],
                ],
            ],
        ]);
    }

    #[Test]
    public function it_can_delete_a_images_for_tour(): void
    {
        $this->createAdmin();
        $travel = Travel::factory()->create();
        $tour = Tour::factory()->create([
            'travel_id' => $travel->id,
        ]);
        $tour->images()
            ->createMany([
                [
                    'path' => 'public/images/tours/679ff490614db7.65605970.jpg',
                ],
                [
                    'path' => 'public/images/tours/679ff490614db7.65605970.jpg',
                ],
            ]);

        $ids = $tour->images->pluck('id')
            ->toArray();
        foreach ($ids as $id) {
            $this->assertDatabaseHas(Image::getTableName(), [
                'id' => $id,
            ]);
        }
        $response = $this->deleteJson(
            route('admin.tours.destroy.files', [
                'travel' => $travel,
                'tour' => $tour,
            ]),
            [
                'images' => $ids,
            ]
        );
        $response->assertStatus(204);
        $this->assertDatabaseCount(Image::getTableName(), 0);
    }

    #[Test]
    public function it_non_admin_user_cannot_access_cud_tour(): void
    {
        $travel = Travel::factory()->create([
            'is_public' => true,
        ]);
        $tour = Tour::factory()->create([
            'travel_id' => $travel->id,
        ]);
        $role = Role::create([
            'name' => RoleEnum::USER->name,
        ]);
        $user = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
        ]);
        $user->roles()
            ->attach($role);
        Sanctum::actingAs($user);

        $response = $this->postJson(route('admin.tours.store', [
            'travel' => $travel,
        ]), $this->getTour());
        $response->assertStatus(403);

        $this->putJson(route('admin.tours.update', [
            'travel' => $travel,
            'tour' => $tour,
        ]), $this->getTour())->assertStatus(403);

        $this->deleteJson(route('admin.tours.destroy', [
            'travel' => $travel,
            'tour' => $tour,
        ]))->assertStatus(403);
    }

    public function createAdmin()
    {
        $role = Role::create([
            'name' => RoleEnum::ADMIN->name,
        ]);
        $user = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
        ]);
        $user->roles()
            ->attach($role);
        Sanctum::actingAs($user);

        return $user;
    }

    public function createEditor()
    {
        $role = Role::create([
            'name' => RoleEnum::EDITOR->name,
        ]);
        $user = User::factory()->create([
            'name' => 'editor',
            'email' => 'editor@editor.com',
            'password' => Hash::make('password'),
        ]);
        $user->roles()
            ->attach($role);
        Sanctum::actingAs($user);

        return $user;
    }

    public function getTour(array $files = []): array
    {
        $data = [
            'name' => 'Tour 1',
            'start_date' => '2019-01-01',
            'end_date' => '2019-01-02',
            'price' => 99.22,
        ];
        if ($files !== []) {
            $data['images'] = $files;
        }

        return $data;
    }
}
