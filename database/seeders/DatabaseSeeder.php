<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Tour;
use App\Models\Travel;
use App\Models\User;

use Illuminate\Database\Seeder;

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
            ['name' => 'admin'],
            ['name' => 'editor'],
        ];
        Role::insert($roles);
    }
}
