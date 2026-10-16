# Etapa 1: inventario previo a la reorganizacion

Fecha del inventario: 2026-10-09.

Este documento caracteriza la estructura actual. No describe una estructura ya refactorizada.

## Estado inicial

- Laravel 11.31+, PHP 8.2+, Livewire 4.3 y Spatie Laravel Permission 6.25.
- Rama observada: `main`.
- Cambios locales preexistentes, conservados sin modificacion por esta etapa:
  - `public/assets/css/custom.css`
  - `resources/views/layouts/app.blade.php`
- Archivos de rutas encontrados: `routes/web.php`, `routes/auth.php`, `routes/console.php` y `routes/channels.php`.
- `php artisan route:list --json` registro 222 rutas: 177 apuntan a controladores de la aplicacion y 45 pertenecen a Laravel, Fortify u otros componentes del framework.

## Modulos y rutas de la aplicacion

| Modulo | Rutas de controladores App | Dependencias principales |
|---|---:|---|
| Autenticacion y acceso | 2 propias, mas rutas Fortify | `GoogleAuthController`, Fortify, Socialite, middleware `guest` |
| Usuarios, roles y permisos | 21 | usuarios, roles, permisos y activity logs; Spatie Permission |
| Dashboard | 1 | `InicioController`, `InicioService`, `InicioPolicy` y Gate `view-inicio` |
| Cuenta | 6 | perfil, avatar, contrasena local y verificacion de celular |
| Catalogo de servicios | 18 | rubros, tipos de servicio y especialidades |
| Proveedores | 64 | administracion de perfiles y gestion del perfil autenticado |
| Solicitudes | 13 | variantes admin, cliente y proveedor; historial de solicitudes |
| Citas | 8 | variantes admin, cliente y proveedor |
| Calificaciones | 14 | aspectos, administracion, cliente y respuestas de proveedor |
| Busqueda inteligente | 14 | busqueda de cliente y administracion del motor FastAPI |
| Notificaciones | 3 | notificaciones recientes y marcado de lectura |
| Reportes | 9 | CRUD, configuracion y generacion PDF |
| Backups | 3 | consulta, configuracion y ejecucion manual |
| General | 1 | landing publica |

Las rutas propias suman 177. Las rutas Fortify de login, registro, recuperacion, verificacion y doble factor se registran adicionalmente desde `routes/auth.php` y el proveedor de Fortify.

## Organizacion actual por actor

- Administrador: 116 rutas apuntan a controladores bajo `App\Http\Controllers\Admin`.
- Cliente: 14 rutas apuntan a controladores bajo `App\Http\Controllers\Cliente`.
- Proveedor: 34 rutas apuntan a controladores bajo `App\Http\Controllers\Proveedor`.
- General: 13 rutas propias apuntan a controladores sin esos namespaces.

La organizacion de controladores es principalmente por actor. Modelos y la mayor parte de los servicios estan organizados por funcionalidad o permanecen planos.

## Referencias a nombres de rutas

Las referencias fueron localizadas en:

- Blade: formularios, enlaces, sidebar, topbar y variantes por actor.
- Livewire de archivo unico: enlaces de edicion y acciones de tablas.
- Controladores: redirecciones posteriores a operaciones CRUD.
- Services: URLs incluidas en notificaciones de solicitudes y calificaciones.
- JavaScript: varias URLs se entregan desde Blade mediante `@json(route(...))`; otros scripts consumen atributos `data-*` preparados por las vistas.

Puntos con alta concentracion de dependencias:

- `resources/views/layouts/partials/sidebar.blade.php` referencia la mayoria de los nombres de rutas de navegacion.
- `routes/web.php` importa todos los controladores de negocio y contiene los bloques Cliente, Proveedor y Administracion.
- `SolicitudService` y `RespuestaCalificacionService` generan URLs nombradas para notificaciones.

Estas referencias deben conservarse o actualizarse de forma atomica durante una reorganizacion futura.

## Autorizacion por modulo

Todas las rutas de negocio de `routes/web.php` se encuentran dentro de `auth` y `verified`, salvo la landing. Los controladores agregan middleware de Spatie por operacion.

| Modulo | Middleware/permisos | Policies o propiedad | Roles directos detectados |
|---|---|---|---|
| Acceso | `ver/crear/editar/eliminar` usuarios, roles y permisos; `asignar rol usuarios`; `ver activity logs` | No hay Policies especificas | `SUPERADMIN` tiene bypass global |
| Dashboard | `auth`, `verified`; Gate `view-inicio` | `InicioPolicy` | `ADMINISTRADOR`, `CLIENTE`, `PROVEEDOR` |
| Catalogo | permisos CRUD de rubros, tipos y especialidades | No hay Policies del catalogo | Ninguno en Rubros |
| Proveedores | permisos CRUD administrativos y permisos `gestionar ... proveedor` | La propiedad se resuelve principalmente en servicios/controladores de mi perfil | Se exige `PROVEEDOR` en consultas y validacion de perfiles |
| Solicitudes | permisos admin, cliente y proveedor | `SolicitudPolicy` e `HistorialSolicitudPolicy`; cliente o perfil proveedor propietario | `SolicitudService` filtra usuarios con rol `CLIENTE` |
| Citas | permisos admin y proveedor, `ver mis citas` | `CitaPolicy`; propiedad por solicitud y perfil proveedor | No es la comprobacion principal |
| Calificaciones | permisos admin, cliente y proveedor | `CalificacionPolicy` y `RespuestaCalificacionPolicy` | No es la comprobacion principal |
| Busqueda | `buscar servicios` y permisos separados del panel del motor | `EjecucionBusquedaMotorPolicy` limita la ejecucion al usuario creador | Sidebar limita busqueda al rol `CLIENTE` |
| Notificaciones | `auth`, `verified` | Controlador filtra notificaciones del usuario autenticado | Ninguno detectado |
| Reportes | permisos ver, crear, editar, eliminar, generar y configurar | Sin Policy especifica | Ninguno detectado |
| Backups | permisos ver, configurar y ejecutar | Sin Policy especifica | Ninguno detectado |

### Observaciones sin corregir en esta etapa

1. Muchos Form Requests devuelven `true` y delegan la seguridad al middleware del controlador.
2. La autorizacion se distribuye entre middleware, Policies, `abort_unless`, Form Requests y Blade.
3. El dashboard selecciona una variante excluyente y depende de nombres de roles fijos.
4. El sidebar combina permisos con comprobaciones directas de `CLIENTE` y `PROVEEDOR`.
5. `UsuarioService::syncUserRole` sincroniza como maximo un rol aunque Spatie soporte varios.
6. La propiedad de recursos esta modelada en Policies para solicitudes, citas y calificaciones, pero no todos los modulos CRUD tienen Policy.
7. Ocultar enlaces mediante `@can` se usa como presentacion; la proteccion real de Rubros reside en middleware de backend.

## Caracterizacion del modulo Rubros

Rutas actuales:

| Metodo | URI | Nombre | Accion | Permiso |
|---|---|---|---|---|
| GET/HEAD | `rubros` | `rubros.index` | `RubroController@index` | `ver rubros` |
| GET/HEAD | `rubros/create` | `rubros.create` | `RubroController@create` | `crear rubros` |
| POST | `rubros` | `rubros.store` | `RubroController@store` | `crear rubros` |
| GET/HEAD | `rubros/{rubro}/edit` | `rubros.edit` | `RubroController@edit` | `editar rubros` |
| PUT/PATCH | `rubros/{rubro}` | `rubros.update` | `RubroController@update` | `editar rubros` |
| DELETE | `rubros/{rubro}` | `rubros.destroy` | `RubroController@destroy` | `eliminar rubros` |

`destroy` no elimina fisicamente: invierte el campo booleano `estado`. La actualizacion no cambia `estado`; ese cambio queda reservado a `destroy` y al componente Livewire actual.

Dependencias de produccion:

- `App\Http\Controllers\Admin\RubroController`
- `App\Http\Requests\StoreRubroRequest`
- `App\Http\Requests\UpdateRubroRequest`
- `App\Services\RubroService`
- `App\Models\Rubro`
- `resources/views/admin/rubros/*`
- `resources/views/components/rubros/tabla-rubros.blade.php`
- `public/assets/js/rubros.js`
- permisos Spatie `ver`, `crear`, `editar` y `eliminar rubros`

No existe una `RubroPolicy`; la proteccion se realiza mediante middleware de permisos. Los Form Requests de Rubros autorizan con `true`.

## Scheduler y rutas de consola

`routes/console.php` conserva:

- comando de desarrollo que inicia servidor, Reverb, cola y Scheduler;
- backup automatico con configuracion almacenada;
- comando `motor:indexaciones-procesar` cada minuto con proteccion contra solapamiento.

Estas tareas no pertenecen a la refactorizacion inicial de Rubros y no deben alterarse.

## Base segura para la siguiente etapa

Antes de mover Rubros se deben mantener verdes las pruebas de caracterizacion. La primera reorganizacion deberia limitarse a agrupar Controller, Requests, Service y vistas bajo el modulo Catalogo de servicios, manteniendo temporalmente todos los nombres de rutas y permisos. No se debe cambiar simultaneamente el comportamiento del CRUD o de la tabla Livewire.

## Ejecucion de pruebas de esta etapa

La extension `pdo_sqlite` no esta disponible en el PHP local. Para impedir cualquier acceso destructivo a `dbintegrador3`, PHPUnit fue configurado para utilizar exclusivamente la base MySQL `dbintegrador3_testing`. Esa base se creo expresamente para pruebas.

Resultados:

- Caracterizacion de Rubros: 11 pruebas aprobadas, 84 aserciones.
- Suite completa: 71 pruebas aprobadas y 3 fallidas, con 301 aserciones procesadas.
- Todas las pruebas de Rubros aprobaron tambien como parte de la suite completa.

Las tres fallas pertenecen al archivo preexistente `tests/Feature/Services/ConfiguracionIndexacionMotorServiceTest.php`:

1. programacion diaria una sola vez por minuto;
2. programacion semanal respetando el dia;
3. calculo de la siguiente ejecucion.

El defecto se reproduce ejecutando ese archivo de forma aislada y no fue causado por Rubros. `ConfiguracionIndexacionMotorService::configuracion()` busca siempre el registro con `id = 1`, pero `id` no es asignable masivamente en `ConfiguracionIndexacionMotor`. Por ello, `firstOrCreate(['id' => 1], ...)` crea un registro con el siguiente autoincremento en vez del identificador 1. La siguiente consulta vuelve a crear una configuracion desactivada y las operaciones programadas retornan `false` o `null`.

No se modifico el servicio ni su modelo porque corregir ese defecto cambiaria codigo de produccion fuera del alcance autorizado.
