<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    // Listar usuarios
    public function index()
    {
        $usuarios = Usuario::with('rol')->get();

        return response()->json($usuarios);
    }

    // Crear usuario
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'correo' => 'required|email|unique:usuarios,correo',
            'password' => 'required|min:6',
            'id_rol' => 'required|exists:roles,id',
            'telefono' => 'nullable|string',
            'direccion' => 'nullable|string'
        ]);

        $request->validate([
            'tipo_documento' => 'required|in:DNI,RUC',
            'numero_documento' => [
                'required',
                'unique:clientes,numero_documento',
                function ($attribute, $value, $fail) use ($request) {

                    if ($request->tipo_documento === 'DNI' && strlen($value) != 8) {
                        $fail('El DNI debe tener 8 dígitos.');
                    }

                    if ($request->tipo_documento === 'RUC' && strlen($value) != 11) {
                        $fail('El RUC debe tener 11 dígitos.');
                    }
                }
            ]
        ]);

        DB::beginTransaction();

        try {

            $usuario = Usuario::create([
                'nombre' => $request->nombre,
                'correo' => $request->correo,
                'password' => Hash::make($request->password),
                'id_rol' => $request->id_rol,
                'activo' => 1
            ]);

            if ($usuario->rol->nombre === 'CLIENTE') {

                Cliente::create([
                    'id_usuario' => $usuario->id,
                    'telefono' => $request->telefono,
                    'direccion' => $request->direccion,
                    'tipo_documento' => $request->tipo_documento,
                    'numero_documento' => $request->numero_documento
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Usuario creado correctamente',
                'usuario' => $usuario->load('rol')
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'error' => 'Error al crear usuario',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    // Activar / Desactivar usuario
    public function toggleActivo($id)
    {
        $usuario = Usuario::findOrFail($id);

        $usuario->activo = !$usuario->activo;
        $usuario->save();

        return response()->json([
            'message' => 'Estado actualizado',
            'activo' => $usuario->activo
        ]);
    }
}
