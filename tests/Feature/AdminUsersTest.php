<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enum\Role as RoleEnum;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_shows_users_list(): void
    {
        $this->createAdmin();

        $roleUser = $this->createRole(RoleEnum::USER->name);
        $users = User::factory(15)->create();

        $users->each(fn (User $user) => $user->roles()->attach($roleUser));

        $response = $this->getJson(route('admin.users.index'));

        $response->assertOk()
            ->assertJsonCount(15, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email', 'roles'],
                ],
            ]);
    }

    #[Test]
    public function it_shows_a_user(): void
    {
        $this->createAdmin();

        $roleUser = $this->createRole(RoleEnum::USER->name);
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@doe.com',
        ]);

        $user->roles()
            ->attach($roleUser);

        $response = $this->getJson(route('admin.users.show', $user));

        $response->assertOk()
            ->assertJsonFragment([
                'name' => 'John Doe',
                'email' => 'john@doe.com',
                'roles' => [
                    [
                        'id' => $roleUser->id,
                        'name' => RoleEnum::USER->name,
                    ],
                ],
            ]);
    }

    #[Test]
    public function it_deletes_a_user(): void
    {
        $this->createAdmin();

        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@doe.com',
        ]);

        $response = $this->deleteJson(route('admin.users.destroy', $user));

        $response->assertNoContent();
        $this->assertModelMissing($user);
    }

    #[Test]
    public function it_restricts_access_to_admin_only(): void
    {
        $roleUser = $this->createRole(RoleEnum::USER->name);
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@doe.com',
        ]);

        $user->roles()
            ->attach($roleUser);
        Sanctum::actingAs($user);

        $routes = [
            route('admin.users.show', $user),
            route('admin.users.index'),
            route('admin.users.destroy', $user),
        ];

        foreach ($routes as $route) {
            $response = $this->getJson($route);
            $response->assertForbidden();
        }
    }

    private function createAdmin(): User
    {
        $roleAdmin = $this->createRole(RoleEnum::ADMIN->name);

        $admin = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
        ]);

        $admin->roles()
            ->attach($roleAdmin);
        Sanctum::actingAs($admin);

        return $admin;
    }

    private function createRole(string $roleName): Role
    {
        return Role::firstOrCreate([
            'name' => $roleName,
        ]);
    }
}
