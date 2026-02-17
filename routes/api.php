<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\OrdenServicioController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VehiculoController;

Route::get('/test', function () {
    return response()->json([
        'message' => 'API funcionando correctamente'
    ]);
});

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'role:ADMIN'])->group(function () {

    // Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::middleware('role:ADMIN')->group(function () {

        Route::post('/ordenes', [OrdenServicioController::class, 'store']);
        Route::post('/usuarios', [UsuarioController::class, 'store']);
        Route::put('/usuarios/{id}/toggle', [UsuarioController::class, 'toggleActivo']);
        Route::post('/ordenes', [OrdenServicioController::class, 'store']);
        Route::get('/ordenes', [OrdenServicioController::class, 'index']);
        Route::get('/vehiculos', [VehiculoController::class, 'index']);

    });;
});

Route::middleware(['auth:sanctum', 'role:ADMIN,CLIENTE'])->group(function () {
    
    Route::get('/ordenes', [OrdenServicioController::class, 'index']);
    Route::get('/ordenes/{id}', [OrdenServicioController::class, 'show']);
    Route::put('/ordenes/{id}/estado', [OrdenServicioController::class, 'updateEstado']);
});

Route::middleware(['auth:sanctum', 'role:MECANICO'])->group(function () {
    
    Route::get('/mecanico-test', function () {
        return response()->json([
            'message' => 'Acceso autorizado como MECANICO'
        ]);
    });
    Route::get('/mis-ordenes', [OrdenServicioController::class, 'misOrdenes']);
    Route::put('/ordenes/{id}/estado', [OrdenServicioController::class, 'updateEstado']);
});

Route::middleware(['auth:sanctum', 'role:CLIENTE'])->group(function () {
    
    Route::get('/cliente-test', function () {
        return response()->json([
            'message' => 'Acceso autorizado como CLIENTE'
        ]);
    });

    //Route::get('/mi-seguimiento', [OrdenServicioController::class, 'miSeguimiento']);
    Route::get('/mis-vehiculos', [VehiculoController::class, 'misVehiculos']);
    Route::get('/vehiculos/{id}/seguimiento', [OrdenServicioController::class, 'seguimientoVehiculo']);
});

Route::middleware(['auth:sanctum', 'role:ADMIN,MECANICO'])->group(function () {

    Route::post('/vehiculos', [VehiculoController::class, 'store']);
    Route::get('/vehiculos', [VehiculoController::class, 'index']);

});