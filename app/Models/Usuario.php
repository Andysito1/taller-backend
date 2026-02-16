<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'usuarios'; // IMPORTANTE

    protected $fillable = [
        'nombre',
        'correo',
        'password',
        'id_rol',
        'activo'
    ];

    protected $hidden = [
        'password'
    ];

    // Indicar que el campo de login es correo
    public function getAuthIdentifierName()
    {
        return 'correo';
    }

    public function rol()
    {
        return $this->belongsTo(Role::class, 'id_rol');
    }
}
