<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Eloquent de productos.
 *
 * Por convención, Laravel relaciona Product con la tabla plural `products`.
 * No hace falta escribir consultas SQL para las operaciones habituales.
 */
class Product extends Model
{
    /** Campos que se pueden guardar juntos con create() o update(). */
    protected $fillable = ['nombre', 'descripcion', 'precio', 'stock', 'categoria', 'activo'];

    /** Convierte los valores de la base a tipos útiles al leerlos. */
    protected function casts(): array
    {
        return [
            'precio' => 'float',
            'stock' => 'integer',
            'activo' => 'boolean',
        ];
    }
}
