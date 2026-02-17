<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehiculo;
use App\Models\Cliente;
use Illuminate\Http\Request;

class VehiculoController extends Controller
{
    // Listar vehículos del cliente autenticado
    public function index(Request $request)
    {
        $vehiculos = \App\Models\Vehiculo::with('cliente.usuario')
            ->paginate(10); // 10 por página

        return response()->json($vehiculos);
    }

    // Vehiculos del cliente para la vista cliente
    public function misVehiculos()
    {
        //Check if user is authenticated
        if (!auth()->check()) {
            return response()->json([
                'message' => 'No autorizado'
            ], 401);
        }
        $user = auth()->user();

        if ($user->rol !== 'CLIENTE') {
            return response()->json([
                'message' => 'No autorizado'
            ], 403);
        }

        $vehiculos = Vehiculo::where('id_cliente', $user->id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $vehiculos
        ]);
    }

    // Crear vehículo
    public function store(Request $request)
    {
        $request->validate([
            'id_cliente' => 'required|exists:clientes,id',
            'marca' => 'required|string|max:50',
            'modelo' => 'required|string|max:50',
            'anio' => 'required|integer',
            'placa' => 'required|string|max:20|unique:vehiculos,placa',
            'imagen' => 'nullable|string'
        ]);

        $vehiculo = Vehiculo::create($request->all());

        return response()->json([
            'message' => 'Vehículo registrado correctamente',
            'vehiculo' => $vehiculo
        ], 201);
    }
}
