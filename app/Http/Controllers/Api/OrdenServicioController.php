<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrdenServicio;
use Illuminate\Http\Request;

class OrdenServicioController extends Controller
{
    // LISTAR TODAS
    public function index(Request $request)
    {
        $query = OrdenServicio::with([
            'vehiculo.cliente.usuario'
        ]);

        // 🔍 Buscar por placa
        if ($request->filled('placa')) {
            $query->whereHas('vehiculo', function ($q) use ($request) {
                $q->where('placa', 'like', '%' . $request->placa . '%');
            });
        }

        // 🔍 Buscar por nombre del cliente
        if ($request->filled('cliente')) {
            $query->whereHas('vehiculo.cliente.usuario', function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->cliente . '%');
            });
        }

        // 🔍 Filtrar por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $ordenes = $query->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $ordenes
        ]);
    }


    // CREAR ORDEN
    public function store(Request $request)
    {
        $request->validate([
            'id_vehiculo' => 'required|exists:vehiculos,id',
            'id_mecanico' => 'required|exists:usuarios,id',
            'titulo' => 'required',
            'descripcion' => 'required',
        ]);

        $orden = OrdenServicio::create([
            'id_vehiculo' => $request->id_vehiculo,
            'id_mecanico' => $request->id_mecanico,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'estado' => 'en_proceso',
            'fecha_inicio' => now()
        ]);

        return response()->json([
            'message' => 'Orden creada correctamente',
            'orden' => $orden
        ], 201);
    }

    // VER DETALLE
    public function show($id)
    {
        $orden = OrdenServicio::with(['vehiculo', 'etapas', 'finanzas'])
                    ->findOrFail($id);

        return response()->json($orden);
    }

    public function updateEstado(Request $request, $id)
    {
        $orden = OrdenServicio::findOrFail($id);
        
        //Check if user is authenticated
        if (!auth()->check()) {
            return response()->json([
                'message' => 'No autorizado'
            ], 401);
        }

        $request->validate([
            'estado' => 'required|in:en_proceso,pausado,finalizado'
        ]);

        // 🔒 Verificar que la orden pertenezca al mecánico logueado
        if ($orden->id_mecanico != $request->user()->id) {
            return response()->json([
                'message' => 'No autorizado para modificar esta orden'
            ], 403);
        }

        $orden->estado = $request->estado;

        if ($request->estado === 'finalizado') {
            $orden->fecha_fin = now();
        }

        $orden->save();

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente',
            'orden' => $orden
        ]);
    }


    // public function miSeguimiento(Request $request)
    // {
    //     // Buscar cliente asociado al usuario logueado
    //     $cliente = \App\Models\Cliente::where('id_usuario', $request->user()->id)->first();

    //     if (!$cliente) {
    //         return response()->json([
    //             'message' => 'Cliente no encontrado'
    //         ], 404);
    //     }

    //     // Traer vehículos y sus órdenes
    //     $vehiculos = \App\Models\Vehiculo::with(['ordenes'])
    //         ->where('id_cliente', $cliente->id)
    //         ->get();

    //     return response()->json([
    //         'success' => true,
    //         'data' => $vehiculos
    //     ]);
    // }

    public function misOrdenes(Request $request)
    {
        $ordenes = \App\Models\OrdenServicio::with('vehiculo')
            ->where('id_mecanico', $request->user()->id)
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $ordenes
        ]);
    }

    public function seguimientoVehiculo($idVehiculo)
    {
        $user = auth()->user();

        if ($user->rol !== 'CLIENTE') {
            return response()->json([
                'message' => 'No autorizado'
            ], 403);
        }

        // Verificar que el vehículo le pertenezca
        $vehiculo = Vehiculo::where('id', $idVehiculo)
            ->where('id_cliente', $user->id)
            ->first();

        if (!$vehiculo) {
            return response()->json([
                'message' => 'Vehículo no encontrado o no pertenece al cliente'
            ], 404);
        }

        $ordenes = OrdenServicio::where('id_vehiculo', $idVehiculo)
            ->with('vehiculo')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $ordenes
        ]);
    }

}
