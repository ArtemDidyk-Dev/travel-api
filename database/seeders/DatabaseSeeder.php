<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Role;
use App\Models\Tour;
use App\Models\Travel;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Enum\Role as RoleEnum;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Travel::factory(16)->create();
        Tour::factory(16)->create();
        $roles = [
            [
                'id' => RoleEnum::ADMIN->value,
                'name' =>  RoleEnum::ADMIN->name
            ],
            [
                'id' => RoleEnum::EDITOR->value,
                'name' =>  RoleEnum::EDITOR->name
            ],
            [
                'id' => RoleEnum::USER->value,
                'name' =>  RoleEnum::USER->name
            ],
        ];
        Role::insert($roles);
        $users = User::factory(10)->create();
        foreach ($users as $user) {
            $user->roles()->attach(RoleEnum::USER->value);
        }
        Comment::factory(10)->create();

    }
}
