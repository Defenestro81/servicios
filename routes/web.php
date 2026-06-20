<?php

use App\Http\Controllers\Admin\TipoEquipoController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\AdjuntoController;
use App\Http\Controllers\ArregloTerceroController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('ordenes.index'));

Route::get('/dashboard', fn() => redirect()->route('ordenes.index'))
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Órdenes (todos los roles autenticados)
    Route::resource('ordenes', OrdenController::class)->parameters(['ordenes' => 'orden']);
    Route::post('ordenes/{orden}/estado', [OrdenController::class, 'cambiarEstado'])->name('ordenes.cambiarEstado');
    Route::post('ordenes/{orden}/tomar', [OrdenController::class, 'tomarOrden'])->name('ordenes.tomar');
    Route::post('ordenes/{orden}/tecnico', [OrdenController::class, 'asignarTecnico'])->name('ordenes.asignarTecnico');

    // Adjuntos y arreglos (nested bajo ordenes)
    Route::post('ordenes/{orden}/adjuntos', [AdjuntoController::class, 'store'])->name('adjuntos.store');
    Route::delete('ordenes/{orden}/adjuntos/{adjunto}', [AdjuntoController::class, 'destroy'])->name('adjuntos.destroy');

    Route::post('ordenes/{orden}/arreglos', [ArregloTerceroController::class, 'store'])->name('arreglos.store');
    Route::patch('ordenes/{orden}/arreglos/{arreglo}', [ArregloTerceroController::class, 'update'])->name('arreglos.update');
    Route::delete('ordenes/{orden}/arreglos/{arreglo}', [ArregloTerceroController::class, 'destroy'])->name('arreglos.destroy');

    // Empresa inline (técnico + admin)
    Route::post('/empresas/inline', [EmpresaController::class, 'storeInline'])->name('empresas.inline');

    // Clientes y equipos (técnico + admin)
    Route::middleware('role:tecnico|administrador')->group(function () {
        Route::resource('clientes', ClienteController::class);
        Route::resource('equipos', EquipoController::class)->except('destroy');
        Route::post('/tipos-equipos/inline', [TipoEquipoController::class, 'storeInline'])->name('tipos-equipos.inline');
    });
});

// Admin
Route::middleware(['auth', 'role:administrador'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/usuarios', [UserRoleController::class, 'index'])->name('users.index');
    Route::patch('/usuarios/{user}/rol', [UserRoleController::class, 'update'])->name('users.role.update');

    Route::get('/tipos-equipos', [TipoEquipoController::class, 'index'])->name('tipos-equipos.index');
    Route::post('/tipos-equipos', [TipoEquipoController::class, 'store'])->name('tipos-equipos.store');
    Route::patch('/tipos-equipos/{tipoEquipo}', [TipoEquipoController::class, 'update'])->name('tipos-equipos.update');
    Route::patch('/tipos-equipos/{tipoEquipo}/toggle', [TipoEquipoController::class, 'toggleActivo'])->name('tipos-equipos.toggle');
});

require __DIR__.'/auth.php';
