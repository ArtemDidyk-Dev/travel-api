<?php

namespace Tests\Feature;

use App\Enum\Role as RoleEnum;
use App\Models\Role;
use App\Models\Travel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminTravelTest extends TestCase
{

    use RefreshDatabase;

    #[Test] public function it_admin_create_travel(): void
    {
        $this->createAdmin();
        $response = $this->postJson(route('admin.travels.store'), $this->getTravel());
        $response->assertCreated();
        $response->assertJsonFragment(['name' => 'travel']);
    }

    #[Test] public function it_admin_update_travel(): void
    {
        $this->createAdmin();
        $response = $this->postJson(route('admin.travels.store'), $this->getTravel());
        $response->assertCreated();
        $response->assertJsonFragment(['name' => 'travel']);
    }

    #[Test] public function it_admin_destroy_travel(): void
    {
        $this->createAdmin();
        $travel = Travel::factory()->create();
        $this->deleteJson(route('admin.travels.destroy', $travel))->assertStatus(204);
        $this->assertModelMissing($travel);
    }

    #[Test] public function it_public_user_cannot_access_curd_travel(): void
    {
        $response = $this->postJson(route('admin.travels.store'), $this->getTravel());
        $response->assertStatus(401);

        $response = $this->postJson(route('admin.travels.store'), $this->getTravel());
        $response->assertStatus(401);

        $travel = Travel::factory()->create();
        $this->deleteJson(route('admin.travels.destroy', $travel))->assertStatus(401);
    }

    #[Test] public function it_non_admin_user_cannot_access_crud_travel(): void
    {
        $role = Role::create(['name' => RoleEnum::USER->name]);
        $user = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
        ]);
        $user->roles()->attach($role);
        Sanctum::actingAs($user);

        $response = $this->postJson(route('admin.travels.store'), $this->getTravel());
        $response->assertStatus(403);

        $response = $this->postJson(route('admin.travels.store'), $this->getTravel());
        $response->assertStatus(403);

        $travel = Travel::factory()->create();
        $this->deleteJson(route('admin.travels.destroy', $travel))->assertStatus(403);
    }

    #[Test] public function it_invalid_data_save_for_travel(): void
    {
        $this->createAdmin();
        $response = $this->postJson(route('admin.travels.store'), [
            'name' => 'travel',
        ]);
        $response->assertStatus(422);
    }

    public function createAdmin()
    {
        $role = Role::create(['name' => RoleEnum::ADMIN->name]);
        $user = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
        ]);
        $user->roles()->attach($role);
        Sanctum::actingAs($user);

        return $user;
    }


    public function getTravel(): array
    {
        return [
            'name' => 'travel',
            'description' => 'travel',
            'is_public' => 1,
            'number_of_days' => 3,
        ];
    }
}
