<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Valida la cantidad de POST /api/productos/{product}/vender. */
class SellProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cantidad' => ['required', 'integer', 'min:1'],
        ];
    }
}
