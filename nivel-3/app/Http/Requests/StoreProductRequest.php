<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Valida los campos obligatorios de POST /api/productos. */
class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La ruta ya exige auth:sanctum; cualquier usuario logueado puede crear.
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'min:3', 'max:150', 'unique:products,nombre'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'precio' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'categoria' => ['required', 'string', 'max:50'],
        ];
    }
}
