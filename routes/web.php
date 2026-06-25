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

    // Órdenes: listado (todos los roles autenticados; el rol "usuario" ve sólo lo suyo)
    Route::get('ordenes', [OrdenController::class, 'index'])->name('ordenes.index');

    // Gestión de órdenes (técnico + admin). Las rutas estáticas van antes de ordenes/{orden}.
    Route::middleware('role:tecnico|administrador')->group(function () {
        Route::get('ordenes/buscar', [OrdenController::class, 'buscar'])->name('ordenes.buscar');
        Route::get('ordenes/mias', [OrdenController::class, 'mias'])->name('ordenes.mias');
        Route::get('ordenes/create', [OrdenController::class, 'create'])->name('ordenes.create');
        Route::post('ordenes', [OrdenController::class, 'store'])->name('ordenes.store');
        Route::get('ordenes/{orden}/edit', [OrdenController::class, 'edit'])->name('ordenes.edit');
        Route::match(['put', 'patch'], 'ordenes/{orden}', [OrdenController::class, 'update'])->name('ordenes.update');
        Route::delete('ordenes/{orden}', [OrdenController::class, 'destroy'])->name('ordenes.destroy');

        Route::post('ordenes/{orden}/estado', [OrdenController::class, 'cambiarEstado'])->name('ordenes.cambiarEstado');
        Route::post('ordenes/{orden}/tomar', [OrdenController::class, 'tomarOrden'])->name('ordenes.tomar');
        Route::post('ordenes/{orden}/tecnico', [OrdenController::class, 'asignarTecnico'])->name('ordenes.asignarTecnico');

        // Adjuntos y arreglos (nested bajo ordenes)
        Route::post('ordenes/{orden}/adjuntos', [AdjuntoController::class, 'store'])->name('adjuntos.store');
        Route::delete('ordenes/{orden}/adjuntos/{adjunto}', [AdjuntoController::class, 'destroy'])->name('adjuntos.destroy');
        Route::post('ordenes/{orden}/arreglos', [ArregloTerceroController::class, 'store'])->name('arreglos.store');
        Route::patch('ordenes/{orden}/arreglos/{arreglo}', [ArregloTerceroController::class, 'update'])->name('arreglos.update');
        Route::delete('ordenes/{orden}/arreglos/{arreglo}', [ArregloTerceroController::class, 'destroy'])->name('arreglos.destroy');

        // Empresa inline (modal de creación rápida)
        Route::post('/empresas/inline', [EmpresaController::class, 'storeInline'])->name('empresas.inline');

        // ABM de clientes, empresas y equipos
        Route::resource('clientes', ClienteController::class);
        Route::resource('empresas', EmpresaController::class)->except('show', 'destroy');
        Route::resource('equipos', EquipoController::class)->except('destroy');
        Route::post('/tipos-equipos/inline', [TipoEquipoController::class, 'storeInline'])->name('tipos-equipos.inline');
    });

    // Órdenes: detalle (todos los roles; el control de propiedad se hace en el controlador)
    Route::get('ordenes/{orden}', [OrdenController::class, 'show'])->name('ordenes.show');
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
