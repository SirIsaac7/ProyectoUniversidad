<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\InicioController;
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
use App\Http\Controllers\MiPerfilProveedor\DocumentoController as MiDocumentoProveedorController;
use App\Http\Controllers\MiPerfilProveedor\EspecialidadController as MiEspecialidadProveedorController;
use App\Http\Controllers\MiPerfilProveedor\HorarioController as MiHorarioProveedorController;
use App\Http\Controllers\MiPerfilProveedor\PerfilController as MiPerfilController;
use App\Http\Controllers\MiPerfilProveedor\PortafolioController as MiPortafolioProveedorController;
use App\Http\Controllers\MiPerfilProveedor\UbicacionController as MiUbicacionProveedorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('inicio');
});

Route::middleware('auth', 'verified')->group(function () {
    Route::get('/inicio', [InicioController::class, 'index'])->name('inicio');
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil.index');
    //Esta rutas son de MI PERFIL PROVEEDOR
    Route::get('/mi-perfil-proveedor', [MiPerfilController::class, 'index'])->name('mi-perfil-proveedor.index');
    Route::put('/mi-perfil-proveedor', [MiPerfilController::class, 'update'])->name('mi-perfil-proveedor.update');
    Route::get('/mi-perfil-proveedor/especialidades', [MiEspecialidadProveedorController::class, 'index'])->name('mi-perfil-proveedor.especialidades.index');
    Route::post('/mi-perfil-proveedor/especialidades', [MiEspecialidadProveedorController::class, 'store'])->name('mi-perfil-proveedor.especialidades.store');
    Route::put('/mi-perfil-proveedor/especialidades/{proveedorEspecialidad}', [MiEspecialidadProveedorController::class, 'update'])->name('mi-perfil-proveedor.especialidades.update');
    Route::patch('/mi-perfil-proveedor/especialidades/{proveedorEspecialidad}/activar', [MiEspecialidadProveedorController::class, 'activar'])->name('mi-perfil-proveedor.especialidades.activar');
    Route::delete('/mi-perfil-proveedor/especialidades/{proveedorEspecialidad}', [MiEspecialidadProveedorController::class, 'destroy'])->name('mi-perfil-proveedor.especialidades.destroy');
    Route::get('/mi-perfil-proveedor/horarios', [MiHorarioProveedorController::class, 'index'])->name('mi-perfil-proveedor.horarios.index');
    Route::post('/mi-perfil-proveedor/horarios', [MiHorarioProveedorController::class, 'store'])->name('mi-perfil-proveedor.horarios.store');
    Route::put('/mi-perfil-proveedor/horarios/{horarioProveedor}', [MiHorarioProveedorController::class, 'update'])->name('mi-perfil-proveedor.horarios.update');
    Route::delete('/mi-perfil-proveedor/horarios/{horarioProveedor}', [MiHorarioProveedorController::class, 'destroy'])->name('mi-perfil-proveedor.horarios.destroy');
    Route::get('/mi-perfil-proveedor/ubicacion', [MiUbicacionProveedorController::class, 'index'])->name('mi-perfil-proveedor.ubicacion.index');
    Route::post('/mi-perfil-proveedor/ubicacion', [MiUbicacionProveedorController::class, 'store'])->name('mi-perfil-proveedor.ubicacion.store');
    Route::get('/mi-perfil-proveedor/portafolio', [MiPortafolioProveedorController::class, 'index'])->name('mi-perfil-proveedor.portafolio.index');
    Route::post('/mi-perfil-proveedor/portafolio', [MiPortafolioProveedorController::class, 'store'])->name('mi-perfil-proveedor.portafolio.store');
    Route::put('/mi-perfil-proveedor/portafolio/{portafolioProveedor}', [MiPortafolioProveedorController::class, 'update'])->name('mi-perfil-proveedor.portafolio.update');
    Route::delete('/mi-perfil-proveedor/portafolio/{portafolioProveedor}', [MiPortafolioProveedorController::class, 'destroy'])->name('mi-perfil-proveedor.portafolio.destroy');
    Route::get('/mi-perfil-proveedor/documentos', [MiDocumentoProveedorController::class, 'index'])->name('mi-perfil-proveedor.documentos.index');
    Route::post('/mi-perfil-proveedor/documentos', [MiDocumentoProveedorController::class, 'store'])->name('mi-perfil-proveedor.documentos.store');
    Route::put('/mi-perfil-proveedor/documentos/{documentoProveedor}', [MiDocumentoProveedorController::class, 'update'])->name('mi-perfil-proveedor.documentos.update');
    Route::delete('/mi-perfil-proveedor/documentos/{documentoProveedor}', [MiDocumentoProveedorController::class, 'destroy'])->name('mi-perfil-proveedor.documentos.destroy');
    //FIN de rutas de MI PERFIL PROVEEDOR
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
