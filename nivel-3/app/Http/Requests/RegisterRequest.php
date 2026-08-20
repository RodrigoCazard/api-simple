<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request de POST /api/registro.
 *
 * En Laravel, un Form Request reúne las reglas básicas de entrada antes de que
 * se ejecute el controller. validated() devuelve solamente campos aprobados.
 */
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El registro es público; no hace falta estar autenticado.
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')],
            'clave' => ['required', 'string', 'min:6', 'max:72'],
        ];
    }
}
