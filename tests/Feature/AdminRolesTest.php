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

class AdminRolesTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_shows_roles_list(): void
    {
        $this->createAdmin();
        $this->createRole(RoleEnum::EDITOR->name);
        $this->createRole(RoleEnum::USER->name);
        $response = $this->getJson(route('admin.roles.index'));
        $response->assertStatus(200);
        $response->assertJsonPath('data.*.name', [RoleEnum::ADMIN->name, RoleEnum::EDITOR->name, RoleEnum::USER->name]);
    }

    #[Test]
    public function it_assign_roles_to_user(): void
    {
        $this->createAdmin();
        $roleEditor = $this->createRole(RoleEnum::EDITOR->name);
        $roleUser = $this->createRole(RoleEnum::USER->name);
        $user = User::factory()->create();
        $user->roles()
            ->attach($roleUser);
        $response = $this->postJson(route('admin.roles.user.add', $user), [
            'roles' => [$roleEditor->id],
        ]);
        $response->assertCreated();
        $response->assertJsonFragment([
            'message' => 'Roles assigned successfully.',
        ]);
        $this->assertTrue($user->roles->contains($roleEditor));
        $this->assertTrue($user->roles->contains($roleUser));
    }

    #[Test]
    public function it_delete_roles_to_user(): void
    {
        $this->createAdmin();
        $roleEditor = $this->createRole(RoleEnum::EDITOR->name);
        $roleUser = $this->createRole(RoleEnum::USER->name);
        $user = User::factory()->create();
        $user->roles()
            ->attach([$roleUser, $roleEditor]);
        $this->assertTrue($user->roles->contains($roleEditor));
        $response = $this->deleteJson(route('admin.roles.user.delete', $user), [
            'roles' => [$roleEditor->id],
        ]);
        $response->assertNoContent();
        $user->refresh();
        $this->assertFalse($user->roles->contains($roleEditor));
    }

    #[Test]
    public function it_add_delete_roles_to_user_no_validation(): void
    {
        $this->createAdmin();
        $roleEditor = $this->createRole(RoleEnum::EDITOR->name);
        $roleUser = $this->createRole(RoleEnum::USER->name);
        $user = User::factory()->create();
        $user->roles()
            ->attach([$roleUser, $roleEditor]);
        $this->postJson(route('admin.roles.user.add', $user), [
            'roles' => [99999],
        ])->assertUnprocessable();

        $this->postJson(route('admin.roles.user.add', $user), [
            'roles' => [99999999999999, 1233333312312312],
        ])->assertUnprocessable();
    }

    #[Test]
    public function it_add_roles_to_user_duplicate_bad_request(): void
    {
        $this->createAdmin();
        $roleEditor = $this->createRole(RoleEnum::EDITOR->name);
        $roleUser = $this->createRole(RoleEnum::USER->name);
        $user = User::factory()->create();
        $user->roles()
            ->attach([$roleUser, $roleEditor]);
        $response = $this->postJson(route('admin.roles.user.add', $user), [
            'roles' => [$roleUser->id, $roleEditor->id],
        ]);
        $response->assertBadRequest();
        $response->assertJsonFragment([
            'message' => 'The user already has the selected role: ' . $roleEditor->name . ', ' . $roleUser->name,
        ]);

    }

    #[Test]
    public function it_restricts_access_to_admin_only(): void
    {
        $roleEditor = $this->createRole(RoleEnum::EDITOR->name);
        $roleUser = $this->createRole(RoleEnum::USER->name);

        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@doe.com',
        ]);

        $user->roles()
            ->attach($roleEditor);
        $this->getJson(route('admin.roles.index'))
            ->assertUnauthorized();
        Sanctum::actingAs($user);
        $this->getJson(route('admin.roles.index'))
            ->assertForbidden();
        $roles = [
            'roles' => [$roleUser->id],
        ];
        $this->postJson(route('admin.roles.user.add', $user), $roles)
            ->assertForbidden();
        $this->deleteJson(route('admin.roles.user.delete', $user))
            ->assertForbidden();

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
