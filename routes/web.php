<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentoProveedorController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PerfilProveedorController;
use App\Http\Controllers\PortafolioProveedorController;
use App\Http\Controllers\ProveedorEspecialidadController;
use App\Http\Controllers\RubroController;
use App\Http\Controllers\TipoServicioController;
use App\Http\Controllers\TipoDocumentoProveedorController;
use App\Http\Controllers\UbicacionProveedorController;
use App\Http\Controllers\EspecialidadController;
use App\Http\Controllers\HorarioProveedorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('dashboard');
});

Route::middleware('auth', 'verified')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil.index');
    Route::resource('permisos', PermissionController::class)->except('show');
    Route::resource('roles', RoleController::class)->except('show');
    Route::resource('usuarios', UsuarioController::class)->except(['show']);
    Route::get('/usuarios/{usuario}/roles', [UsuarioController::class, 'editRoles'])->name('usuarios.roles.edit');
    Route::put('/usuarios/{usuario}/roles', [UsuarioController::class, 'updateRoles'])->name('usuarios.roles.update');
    Route::get('/perfil/contrasena-local', [PerfilController::class, 'editLocalPassword'])->name('perfil.password-local.edit');
    Route::put('/perfil/contrasena-local', [PerfilController::class, 'updateLocalPassword'])->name('perfil.password-local.update');
    Route::get('/activitylogs', [ActivityLogController::class, 'index'])->name('activitylogs.index');
    Route::resource('rubros', RubroController::class)->except('show');
    Route::resource('tipos-servicio', TipoServicioController::class)->except('show');
    Route::resource('especialidades', EspecialidadController::class)->except('show');
    Route::resource('perfiles-proveedores', PerfilProveedorController::class)->except('show');
    Route::resource('proveedor-especialidades', ProveedorEspecialidadController::class)->except('show');
    Route::resource('horarios-proveedor', HorarioProveedorController::class)->except('show');
    Route::resource('ubicaciones-proveedor', UbicacionProveedorController::class)->except('show');
    Route::resource('portafolio-proveedor', PortafolioProveedorController::class)->except('show');
    Route::resource('tipos-documento-proveedor', TipoDocumentoProveedorController::class)->except('show');
    Route::resource('documentos-proveedor', DocumentoProveedorController::class)->except('show');
});
