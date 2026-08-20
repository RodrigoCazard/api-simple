<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Modelo Eloquent del usuario.
 *
 * Eloquent convierte cada fila de la tabla `users` en un objeto User. Las
 * propiedades Fillable se pueden cargar en conjunto con User::create(). Hidden
 * evita que la contraseña aparezca cuando el objeto se convierte a JSON.
 */
class User extends Authenticatable
{
    /** Campos que se pueden guardar juntos con create() o update(). */
    protected $fillable = ['nombre', 'email', 'password', 'rol', 'activo'];

    /** Campos que nunca deben aparecer en una respuesta JSON. */
    protected $hidden = ['password'];

    /**
     * Indica a qué tipo debe convertir Eloquent cada atributo.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // Laravel aplica Hash::make() automáticamente al asignar password.
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    /** Una regla de rol pequeña que reutilizan los middleware y controladores. */
    public function isAdmin(): bool
    {
        return $this->rol === 'admin';
    }
}
