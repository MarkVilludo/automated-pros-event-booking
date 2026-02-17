<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $validated): array
    {
        $user = User::create($validated);
        $token = $user->createToken('auth')->plainTextToken;
        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    public function login(string $email, string $password): array
    {
        if (! Auth::attempt(compact('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        $user = User::where('email', $email)->firstOrFail();
        $token = $user->createToken('auth')->plainTextToken;
        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
