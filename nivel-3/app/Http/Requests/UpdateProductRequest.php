<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Valida únicamente los campos presentes en PUT /api/productos/{product}. */
class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // sometimes significa: validar el campo solamente si fue enviado.
        return [
            'nombre' => [
                'sometimes',
                'required',
                'string',
                'min:3',
                'max:150',
                // El nombre debe ser único, excepto el del producto que se edita.
                Rule::unique('products', 'nombre')->ignore($this->route('product')),
            ],
            'descripcion' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'precio' => ['sometimes', 'required', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0'],
            'categoria' => ['sometimes', 'required', 'string', 'max:50'],
            'activo' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
