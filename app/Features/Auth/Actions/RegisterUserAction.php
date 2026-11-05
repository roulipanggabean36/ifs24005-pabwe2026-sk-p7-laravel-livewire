<?php

namespace App\Features\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RegisterUserAction
{
    /** Register selalu berperan member, lalu langsung login. */
    public function execute(array $data): User
    {
        $user = User::create([
            'name'     => $data['name'],
            'username' => $data['username'],
            'email'    => $data['email'],
            'password' => $data['password'],
            'role'     => 'member',
        ]);

        Auth::login($user);

        return $user;
    }
}