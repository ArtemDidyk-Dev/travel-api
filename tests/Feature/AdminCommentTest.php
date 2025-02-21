<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enum\Role as RoleEnum;
use App\Models\Comment;
use App\Models\Image;
use App\Models\Role;
use App\Models\Tour;
use App\Models\Travel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminCommentTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->createAdmin();
    }

    #[Test]
    public function it_admin_show_all_comment(): void
    {
        $travel = Travel::factory()->create([
            'is_public' => true,
        ]);
        $tour = Tour::factory()->create([
            'travel_id' => $travel->id,
        ]);
        Comment::factory(16)->create([
            'tour_id' => $tour->id,
        ]);
        $response = $this->getJson(route('admin.comments.index'));
        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 16)
            ->assertJsonPath('meta.current_page', 1);
    }

    #[Test]
    public function it_public_user_cannot_access_show_all_comment(): void
    {
        $this->createUser();
        $travel = Travel::factory()->create([
            'is_public' => true,
        ]);
        $tour = Tour::factory()->create([
            'travel_id' => $travel->id,
        ]);
        Comment::factory(16)->create([
            'tour_id' => $tour->id,
        ]);
        $response = $this->getJson(route('admin.comments.index'));
        $response->assertStatus(403);
    }

    #[Test]
    public function it_admin_show_comment(): void
    {
        $travel = Travel::factory()->create([
            'is_public' => true,
        ]);
        $tour = Tour::factory()->create([
            'travel_id' => $travel->id,
        ]);
        $comment = Comment::factory()->create([
            'tour_id' => $tour->id,
        ]);
        $response = $this->getJson(route('admin.comments.show', $comment));
        $response->assertOk()
            ->assertJsonStructure([
               'data' => [
                   'id',
                   'text',
                   'images',
                   'is_public',
                   'created_at',
                   'user',
                   'tour' => [
                       'id',
                       'name',
                       'price',
                       'start_date',
                       'end_date',
                   ],
               ]
           ]);
    }

    #[Test]
    public function it_admin_destroy_comment(): void
    {
        $travel = Travel::factory()->create([
            'is_public' => true,
        ]);
        $tour = Tour::factory()->create([
            'travel_id' => $travel->id,
        ]);
        $comment = Comment::factory()->create([
            'tour_id' => $tour->id,
        ]);

        $response = $this->deleteJson(route('admin.comments.destroy', $comment));
        $response->assertNoContent();
        $this->assertDatabaseMissing(Comment::getTableName(), []);
    }


    #[Test]
    public function it_can_delete_a_images_for_comment(): void
    {
        $travel = Travel::factory()->create();
        $tour = Tour::factory()->create([
            'travel_id' => $travel->id,
        ]);
        $comment = Comment::factory()->create([
            'tour_id' => $tour->id,
        ]);
        $comment->images()
            ->createMany([
                [
                    'path' => 'public/images/tours/679ff490614db7.65605970.jpg',
                ],
                [
                    'path' => 'public/images/tours/679ff490614db7.65605970.jpg',
                ],
            ]);

        $ids =  $comment->images->pluck('id')
            ->toArray();
        foreach ($ids as $id) {
            $this->assertDatabaseHas(Image::getTableName(), [
                'id' => $id,
            ]);
        }
        $response = $this->deleteJson(
            route('admin.comments.destroy.files', [
                'comment' => $comment,
            ]),
            [
                'images' => $ids,
            ]
        );
        $response->assertStatus(204);
        $this->assertDatabaseCount(Image::getTableName(), 0);
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
