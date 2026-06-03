<?php

namespace App\Modules\Auth\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(string $login, string $password, ?string $deviceName = null): array
    {
        $user = User::where('activo', true)
            ->where(function ($q) use ($login) {
                $q->where('username', $login)
                  ->orWhere('email', $login)
                  ->orWhere('dni', $login)
                  ->orWhere('ruc', $login);
            })
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Credenciales inválidas.'],
            ]);
        }

        $deviceName ??= request()->userAgent() ?? 'api';
        $token = $user->createToken($deviceName)->plainTextToken;

        $user->ultimo_acceso = now();
        $user->save();

        return [
            'token' => $token,
            'user' => $user->fresh(),
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
