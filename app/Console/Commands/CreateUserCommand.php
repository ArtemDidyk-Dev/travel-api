<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $data['name'] = $this->ask(question: 'What is your name?');
        $data['email'] = $this->ask(question: 'What is your email?');
        $data['password'] = $this->secret(question: 'What is your password?');
        $roleName = $this->choice(question: 'What is your role?', choices: ['admin', 'editor'], default: 1);
        $role = Role::where('name', $roleName)->first();
        if (! $role) {
            $this->error('Role not found');
            return 1;
        }

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return 1;
        }

        DB::transaction(static function () use ($data, $role) {
            $data['password'] = Hash::make($data['password']);
            $user = User::create($data);
            $user->roles()
                ->attach($role->id);
        });

        $this->info('User created');
        return 0;
    }
}
