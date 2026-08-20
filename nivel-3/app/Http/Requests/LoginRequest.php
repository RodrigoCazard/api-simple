<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Valida únicamente los datos básicos de POST /api/login. */
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'clave' => ['required', 'string'],
        ];
    }
}
