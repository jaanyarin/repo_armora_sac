<?php

namespace App\Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $login = (string) $this->input('login', '');
        $loginRules = ['required', 'string', 'max:100'];

        if (is_numeric($login)) {
            $loginRules[] = match (strlen($login)) {
                8 => 'regex:/^\d{8}$/',
                11 => 'regex:/^\d{11}$/',
                default => 'not_regex:/^\d+$/',
            };
        }

        return [
            'login' => $loginRules,
            'password' => ['required', 'string', 'min:6'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'login.required' => 'El usuario, correo, DNI o RUC es obligatorio.',
            'login.regex' => 'El DNI debe tener 8 dígitos o el RUC debe tener 11 dígitos.',
            'login.not_regex' => 'Si es numérico, el DNI debe tener 8 dígitos o el RUC debe tener 11 dígitos.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
        ];
    }
}
