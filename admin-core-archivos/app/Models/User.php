<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Usuario del sistema. Se mapea a la tabla "usuarios" (en lugar de "users")
 * para cumplir con el diseño de la base de datos del taller.
 */
class User extends Authenticatable
{
    protected $table = 'usuarios';

    protected $fillable = ['nombre', 'email', 'password', 'rol'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }
}
