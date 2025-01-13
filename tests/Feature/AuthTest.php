<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enum\Role as RoleEnum;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_user_can_register(): void
    {
        $role = Role::create([
            'name' => RoleEnum::USER->name,
        ]);
        $response = $this->postJson(route('auth.register'), [
            'name' => 'John Doe',
            'email' => 'john@doe.com',
            'password' => 'password12345',
        ]);
        $response->assertStatus(201)
            ->assertJsonStructure(['token']);
        $user = User::where('email', 'john@doe.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->roles->contains($role));
        $this->assertEquals('john@doe.com', $user->email);
    }

    #[Test]
    public function it_registration_fails_with_invalid_data(): void
    {
        $response = $this->postJson(route('auth.register'), [
            'name' => 'John Doe',
            'email' => 'john@doe.com',
            'password' => 'p',
        ]);
        $response->assertStatus(422)
            ->assertJsonStructure([
                'errors' => ['password'],
            ]);
        $response = $this->postJson(route('auth.register'), [
            'name' => '',
            'email' => 'john@doe.com',
            'password' => 'p23123',
        ]);
        $response->assertStatus(422)
            ->assertJsonStructure([
                'errors' => ['name'],
            ]);

        $response = $this->postJson(route('auth.register'), [
            'name' => 'john@doe.com',
            'email' => '',
            'password' => 'p23123',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'errors' => ['email'],
            ]);
    }

    #[Test]
    public function it_user_can_login(): void
    {
        $role = Role::create([
            'name' => RoleEnum::USER->name,
        ]);
        $user = User::factory()->create([
            'email' => 'john.doe@example.com',
            'password' => Hash::make('password12345'),
        ]);
        $user->roles()
            ->attach($role);
        $response = $this->postJson(route('auth.login'), [
            'email' => 'john.doe@example.com',
            'password' => 'password12345',
        ]);
        $response->assertStatus(201)
            ->assertJsonStructure(['token']);
        $user = User::where('email', 'john.doe@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->roles->contains($role));
        $this->assertEquals('john.doe@example.com', $user->email);
        $this->assertNotNull($user->tokens->first());
    }

    #[Test]
    public function it_login_fails_when_user_does_not_exist(): void
    {
        $role = Role::create([
            'name' => RoleEnum::USER->name,
        ]);
        $user = User::factory()->create([
            'email' => 'john.doe@example.com',
            'password' => Hash::make('password12345'),
        ]);
        $user->roles()
            ->attach($role);

        $this->postJson(route('auth.login'), [
            'email' => 'john.doe@example.com',
            'password' => 'password12345676654',
        ])->assertStatus(401)
            ->assertJson([
                'message' => 'Wrong email or password',
            ]);
    }

    #[Test]
    public function it_logout_successful(): void
    {

        $role = Role::create([
            'name' => RoleEnum::USER->name,
        ]);
        $user = User::factory()->create([
            'email' => 'john.doe@example.com',
            'password' => Hash::make('password12345'),
        ]);
        $user->roles()
            ->attach($role);
        $token = $user->createToken('Authentification Token')
            ->plainTextToken;
        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->postJson(route('auth.logout'));
        $response->assertNoContent();
    }
}
