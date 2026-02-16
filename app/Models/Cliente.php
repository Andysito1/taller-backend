<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';

    protected $fillable = [
        'id_usuario',
        'telefono',
        'direccion',
        'tipo_documento',
        'numero_documento'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'id_cliente');
    }
}
