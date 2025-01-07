<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateUserCommandTest extends TestCase
{
    use RefreshDatabase;


    #[Test] public function it_creates_a_user_with_valid_data(): void
    {

        $role = Role::create(['name' => 'admin']);


        $this->artisan('create:user')
            ->expectsQuestion('What is your name?', 'John Doe')
            ->expectsQuestion('What is your email?', 'john@example.com')
            ->expectsQuestion('What is your password?', 'secret123')
            ->expectsChoice('What is your role?', 'admin', ['admin', 'editor'])
            ->assertExitCode(0);

        $user = User::where('email', 'john@example.com')->first();

        $this->assertNotNull($user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertTrue(Hash::check('secret123', $user->password));

        $this->assertTrue($user->roles->contains($role));
    }


    #[Test]   public function it_fails_if_validation_fails()
    {
        $this->artisan('create:user')
            ->expectsQuestion('What is your name?', '')
            ->expectsQuestion('What is your email?', 'john@example.com')
            ->expectsQuestion('What is your password?', '123')
            ->expectsChoice('What is your role?', 'admin1', ['admin', 'editor'])
            ->assertExitCode(1);
        $this->assertNull(User::where('email', 'john@example.com')->first());
    }


    #[Test] public function it_displays_error_if_role_not_found()
    {

        $this->artisan('create:user')
            ->expectsQuestion('What is your name?', 'John Doe')
            ->expectsQuestion('What is your email?', 'john@example.com')
            ->expectsQuestion('What is your password?', 'secret123')
            ->expectsChoice('What is your role?', 'nonexistent_role', ['admin', 'editor'])
            ->assertExitCode(1);
        $this->assertNull(User::where('email', 'john@example.com')->first());
    }
}
