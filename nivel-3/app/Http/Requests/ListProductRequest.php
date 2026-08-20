<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Valida el filtro opcional de GET /api/productos?categoria=. */
class ListProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'categoria' => ['nullable', 'string', 'max:50'],
        ];
    }
}
