<?php

use App\Http\Controllers\Acceso\ActivityLogController;
use App\Http\Controllers\Acceso\PermissionController;
use App\Http\Controllers\Acceso\RoleController;
use App\Http\Controllers\Acceso\UsuarioController;
use App\Http\Controllers\Calificaciones\Administracion\AspectoCalificacionController;
use App\Http\Controllers\Backups\BackupController;
use App\Http\Controllers\Calificaciones\Administracion\CalificacionController;
use App\Http\Controllers\Citas\Administracion\CitaController;
use App\Http\Controllers\Proveedores\Administracion\DocumentoProveedorController;
use App\Http\Controllers\Solicitudes\Administracion\HistorialSolicitudController;
use App\Http\Controllers\Proveedores\Administracion\HorarioProveedorController;
use App\Http\Controllers\BusquedaInteligente\Administracion\MotorBusquedaController;
use App\Http\Controllers\Proveedores\Administracion\PerfilProveedorController;
use App\Http\Controllers\Proveedores\Administracion\PortafolioProveedorController;
use App\Http\Controllers\Proveedores\Administracion\ProveedorEspecialidadController;
use App\Http\Controllers\Reportes\ReporteController;
use App\Http\Controllers\Solicitudes\Administracion\SolicitudController;
use App\Http\Controllers\Proveedores\Administracion\TipoDocumentoProveedorController;
use App\Http\Controllers\Proveedores\Administracion\UbicacionProveedorController;
use App\Http\Controllers\CatalogoServicios\EspecialidadController;
use App\Http\Controllers\CatalogoServicios\RubroController;
use App\Http\Controllers\CatalogoServicios\TipoServicioController;
use App\Http\Controllers\BusquedaInteligente\Publica\BusquedaServicioController as ClienteBusquedaServicioController;
use App\Http\Controllers\Calificaciones\Cliente\CalificacionController as ClienteCalificacionController;
use App\Http\Controllers\Citas\Cliente\CitaController as ClienteCitaController;
use App\Http\Controllers\Solicitudes\Cliente\HistorialSolicitudController as ClienteHistorialSolicitudController;
use App\Http\Controllers\Solicitudes\Cliente\SolicitudController as ClienteSolicitudController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Notificaciones\NotificacionController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\Calificaciones\Proveedor\CalificacionController as ProveedorCalificacionController;
use App\Http\Controllers\Citas\Proveedor\CitaController as ProveedorCitaController;
use App\Http\Controllers\Proveedores\MiPerfil\DocumentoController as MiDocumentoProveedorController;
use App\Http\Controllers\Proveedores\MiPerfil\EspecialidadController as MiEspecialidadProveedorController;
use App\Http\Controllers\Solicitudes\Proveedor\HistorialSolicitudController as ProveedorHistorialSolicitudController;
use App\Http\Controllers\Proveedores\MiPerfil\HorarioController as MiHorarioProveedorController;
use App\Http\Controllers\Proveedores\MiPerfil\PerfilController as MiPerfilController;
use App\Http\Controllers\Proveedores\MiPerfil\PortafolioController as MiPortafolioProveedorController;
use App\Http\Controllers\Solicitudes\Proveedor\SolicitudController as ProveedorSolicitudController;
use App\Http\Controllers\Proveedores\MiPerfil\UbicacionController as MiUbicacionProveedorController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return redirect('inicio');
// });

Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::middleware('auth', 'verified')->group(function () {
    // Inicio y perfil de usuario
    Route::get('/inicio', [InicioController::class, 'index'])->name('inicio');
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil.index');
    Route::patch('/perfil/avatar', [PerfilController::class, 'updateAvatar'])->name('perfil.avatar.update');
    Route::get('/perfil/contrasena-local', [PerfilController::class, 'editLocalPassword'])->name('perfil.password-local.edit');
    Route::put('/perfil/contrasena-local', [PerfilController::class, 'updateLocalPassword'])->name('perfil.password-local.update');
    Route::post('/perfil/celular/enviar-codigo', [PerfilController::class, 'enviarCodigoCelular'])->name('perfil.celular.enviar-codigo');
    Route::post('/perfil/celular/verificar', [PerfilController::class, 'verificarCelular'])->name('perfil.celular.verificar');
    // Notificaciones
    Route::get('/notificaciones/recientes', [NotificacionController::class, 'recientes'])
        ->name('notificaciones.recientes');
    Route::patch('/notificaciones/{id}/leer', [NotificacionController::class, 'marcarLeida'])
        ->name('notificaciones.leer');
    Route::patch('/notificaciones/leer-todas', [NotificacionController::class, 'marcarTodasLeidas'])
        ->name('notificaciones.leer-todas');

    // Perfil del proveedor autenticado
    Route::prefix('mi-perfil-proveedor')
        ->name('mi-perfil-proveedor.')
        ->group(function () {
            Route::get('/', [MiPerfilController::class, 'index'])->name('index');
            Route::put('/', [MiPerfilController::class, 'update'])->name('update');

            Route::get('/especialidades', [MiEspecialidadProveedorController::class, 'index'])->name('especialidades.index');
            Route::post('/especialidades', [MiEspecialidadProveedorController::class, 'store'])->name('especialidades.store');
            Route::put('/especialidades/{proveedorEspecialidad}', [MiEspecialidadProveedorController::class, 'update'])->name('especialidades.update');
            Route::patch('/especialidades/{proveedorEspecialidad}/activar', [MiEspecialidadProveedorController::class, 'activar'])->name('especialidades.activar');
            Route::delete('/especialidades/{proveedorEspecialidad}', [MiEspecialidadProveedorController::class, 'destroy'])->name('especialidades.destroy');

            Route::get('/horarios', [MiHorarioProveedorController::class, 'index'])->name('horarios.index');
            Route::post('/horarios', [MiHorarioProveedorController::class, 'store'])->name('horarios.store');
            Route::put('/horarios/{horarioProveedor}', [MiHorarioProveedorController::class, 'update'])->name('horarios.update');
            Route::delete('/horarios/{horarioProveedor}', [MiHorarioProveedorController::class, 'destroy'])->name('horarios.destroy');

            Route::get('/ubicacion', [MiUbicacionProveedorController::class, 'index'])->name('ubicacion.index');
            Route::post('/ubicacion', [MiUbicacionProveedorController::class, 'store'])->name('ubicacion.store');

            Route::get('/portafolio', [MiPortafolioProveedorController::class, 'index'])->name('portafolio.index');
            Route::post('/portafolio', [MiPortafolioProveedorController::class, 'store'])->name('portafolio.store');
            Route::put('/portafolio/{portafolioProveedor}', [MiPortafolioProveedorController::class, 'update'])->name('portafolio.update');
            Route::patch('/portafolio/{portafolioProveedor}/activar', [MiPortafolioProveedorController::class, 'activar'])->name('portafolio.activar');
            Route::delete('/portafolio/{portafolioProveedor}', [MiPortafolioProveedorController::class, 'destroy'])->name('portafolio.destroy');

            Route::get('/documentos', [MiDocumentoProveedorController::class, 'index'])->name('documentos.index');
            Route::post('/documentos', [MiDocumentoProveedorController::class, 'store'])->name('documentos.store');
            Route::put('/documentos/{documentoProveedor}', [MiDocumentoProveedorController::class, 'update'])->name('documentos.update');
            Route::delete('/documentos/{documentoProveedor}', [MiDocumentoProveedorController::class, 'destroy'])->name('documentos.destroy');
        });

    // Cliente
    Route::prefix('cliente')
        ->name('cliente.')
        ->group(function () {
            Route::get('/buscar-servicios', [ClienteBusquedaServicioController::class, 'index'])->name('buscar-servicios.index');
            Route::post('/buscar-servicios/semantica', [ClienteBusquedaServicioController::class, 'semantica'])
                ->middleware('throttle:10,1')
                ->name('buscar-servicios.semantica');
            Route::get('/buscar-servicios/semantica/{ejecucion}/estado', [ClienteBusquedaServicioController::class, 'estadoSemantica'])
                ->middleware('throttle:120,1')
                ->name('buscar-servicios.semantica.estado');
            Route::get('/buscar-servicios/semantica/{ejecucion}/resultados', [ClienteBusquedaServicioController::class, 'resultadosSemantica'])
                ->middleware('throttle:30,1')
                ->name('buscar-servicios.semantica.resultados');
            Route::post('/buscar-servicios/transcribir', [ClienteBusquedaServicioController::class, 'transcribir'])->name('buscar-servicios.transcribir');
            Route::post('/buscar-servicios/inteligente', [ClienteBusquedaServicioController::class, 'inteligente'])->name('buscar-servicios.inteligente');
            Route::get('/solicitudes', [ClienteSolicitudController::class, 'index'])->name('solicitudes.index');
            Route::post('/solicitudes', [ClienteSolicitudController::class, 'store'])->name('solicitudes.store');
            Route::delete('/solicitudes/{solicitud}', [ClienteSolicitudController::class, 'destroy'])->name('solicitudes.destroy');

            Route::get('/citas', [ClienteCitaController::class, 'index'])->name('citas.index');
            Route::get('/historial-solicitudes', [ClienteHistorialSolicitudController::class, 'index'])->name('historial-solicitudes.index');
            Route::get('/solicitudes/{solicitud}/historial', [ClienteHistorialSolicitudController::class, 'show'])->name('solicitudes.historial');
            Route::get('/calificaciones', [ClienteCalificacionController::class, 'index'])->name('calificaciones.index');
            Route::post('/calificaciones', [ClienteCalificacionController::class, 'store'])->name('calificaciones.store');
        });

    // Proveedor
    Route::prefix('proveedor')
        ->name('proveedor.')
        ->group(function () {
            Route::get('/solicitudes', [ProveedorSolicitudController::class, 'index'])->name('solicitudes.index');
            Route::patch('/solicitudes/{solicitud}/estado', [ProveedorSolicitudController::class, 'cambiarEstado'])->name('solicitudes.estado');

            Route::get('/citas', [ProveedorCitaController::class, 'index'])->name('citas.index');
            Route::post('/citas', [ProveedorCitaController::class, 'store'])->name('citas.store');
            Route::put('/citas/{cita}', [ProveedorCitaController::class, 'update'])->name('citas.update');
            Route::patch('/citas/{cita}/estado', [ProveedorCitaController::class, 'cambiarEstado'])->name('citas.estado');
            Route::delete('/citas/{cita}', [ProveedorCitaController::class, 'destroy'])->name('citas.destroy');

            Route::get('/historial-solicitudes', [ProveedorHistorialSolicitudController::class, 'index'])->name('historial-solicitudes.index');
            Route::get('/solicitudes/{solicitud}/historial', [ProveedorHistorialSolicitudController::class, 'show'])->name('solicitudes.historial');
            Route::get('/calificaciones', [ProveedorCalificacionController::class, 'index'])->name('calificaciones.index');
            Route::post('/calificaciones/respuestas', [ProveedorCalificacionController::class, 'storeRespuesta'])->name('calificaciones.respuestas.store');
            Route::put('/calificaciones/respuestas/{respuestaCalificacion}', [ProveedorCalificacionController::class, 'updateRespuesta'])->name('calificaciones.respuestas.update');
        });

    // Administracion
    Route::group([], function () {
        Route::resource('permisos', PermissionController::class)->except('show');
        Route::resource('roles', RoleController::class)->except('show');
        Route::resource('usuarios', UsuarioController::class)->except(['show']);
        Route::get('/usuarios/{usuario}/roles', [UsuarioController::class, 'editRoles'])->name('usuarios.roles.edit');
        Route::put('/usuarios/{usuario}/roles', [UsuarioController::class, 'updateRoles'])->name('usuarios.roles.update');
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

        Route::resource('solicitudes', SolicitudController::class)->only(['index', 'destroy']);
        Route::get('citas', [CitaController::class, 'index'])->name('citas.index');
        Route::delete('citas/{cita}', [CitaController::class, 'destroy'])->name('citas.destroy');
        Route::get('historial-solicitudes', [HistorialSolicitudController::class, 'index'])->name('historial-solicitudes.index');
        Route::get('solicitudes/{solicitud}/historial', [HistorialSolicitudController::class, 'show'])->name('solicitudes.historial');

        Route::resource('aspectos-calificacion', AspectoCalificacionController::class)
            ->parameters(['aspectos-calificacion' => 'aspecto_calificacion'])
            ->except('show');
        Route::resource('calificaciones', CalificacionController::class)
            ->parameters(['calificaciones' => 'calificacion'])
            ->only(['index', 'update', 'destroy']);

        Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
        Route::put('backups', [BackupController::class, 'update'])->name('backups.update');
        Route::post('backups/run', [BackupController::class, 'run'])->name('backups.run');

        Route::get('motor-busqueda', [MotorBusquedaController::class, 'index'])->name('motor-busqueda.index');
        Route::get('motor-busqueda/estado', [MotorBusquedaController::class, 'estado'])->name('motor-busqueda.estado');
        Route::post('motor-busqueda/indexacion', [MotorBusquedaController::class, 'iniciarIndexacion'])->name('motor-busqueda.indexacion.store');
        Route::get('motor-busqueda/indexacion/{identificador}', [MotorBusquedaController::class, 'consultarIndexacion'])
            ->where('identificador', '[A-Za-z0-9._:-]+')
            ->name('motor-busqueda.indexacion.show');
        Route::put('motor-busqueda/programacion', [MotorBusquedaController::class, 'actualizarProgramacion'])->name('motor-busqueda.programacion.update');
        Route::get('motor-busqueda/historial/{ejecucion}', [MotorBusquedaController::class, 'detalleHistorial'])->name('motor-busqueda.historial.show');
        Route::put('motor-busqueda/personalizacion', [MotorBusquedaController::class, 'actualizarPersonalizacion'])->name('motor-busqueda.personalizacion.update');
        Route::delete('motor-busqueda/personalizacion', [MotorBusquedaController::class, 'restaurarPersonalizacion'])->name('motor-busqueda.personalizacion.reset');

        Route::get('reportes/configuracion', [ReporteController::class, 'configuracion'])->name('reportes.configuracion');
        Route::put('reportes/configuracion', [ReporteController::class, 'actualizarConfiguracion'])->name('reportes.configuracion.update');
        Route::get('reportes/{reporte}/pdf', [ReporteController::class, 'pdf'])->name('reportes.pdf');
        Route::resource('reportes', ReporteController::class)->except('show');
    });

});
