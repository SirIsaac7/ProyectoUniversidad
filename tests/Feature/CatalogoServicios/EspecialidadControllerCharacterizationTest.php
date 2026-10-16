<?php

namespace Tests\Feature\CatalogoServicios;

use App\Models\Especialidad;
use App\Models\Permission;
use App\Models\Rubro;
use App\Models\RubroTipoServicio;
use App\Models\TipoServicio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class EspecialidadControllerCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_las_rutas_conservan_metodos_controlador_y_middleware_actuales(): void
    {
        $expectedRoutes = [
            'especialidades.index' => [
                'methods' => ['GET', 'HEAD'],
                'action' => 'App\\Http\\Controllers\\CatalogoServicios\\EspecialidadController@index',
                'permission' => 'permission:ver especialidades',
            ],
            'especialidades.create' => [
                'methods' => ['GET', 'HEAD'],
                'action' => 'App\\Http\\Controllers\\CatalogoServicios\\EspecialidadController@create',
                'permission' => 'permission:crear especialidades',
            ],
            'especialidades.store' => [
                'methods' => ['POST'],
                'action' => 'App\\Http\\Controllers\\CatalogoServicios\\EspecialidadController@store',
                'permission' => 'permission:crear especialidades',
            ],
            'especialidades.edit' => [
                'methods' => ['GET', 'HEAD'],
                'action' => 'App\\Http\\Controllers\\CatalogoServicios\\EspecialidadController@edit',
                'permission' => 'permission:editar especialidades',
            ],
            'especialidades.update' => [
                'methods' => ['PUT', 'PATCH'],
                'action' => 'App\\Http\\Controllers\\CatalogoServicios\\EspecialidadController@update',
                'permission' => 'permission:editar especialidades',
            ],
            'especialidades.destroy' => [
                'methods' => ['DELETE'],
                'action' => 'App\\Http\\Controllers\\CatalogoServicios\\EspecialidadController@destroy',
                'permission' => 'permission:eliminar especialidades',
            ],
        ];

        foreach ($expectedRoutes as $name => $expected) {
            $route = Route::getRoutes()->getByName($name);

            $this->assertNotNull($route, "La ruta [{$name}] debe continuar registrada.");
            $this->assertSame($expected['methods'], $route->methods());
            $this->assertSame($expected['action'], $route->getActionName());
            $this->assertContains('auth', $route->gatherMiddleware());
            $this->assertContains('verified', $route->gatherMiddleware());
            $this->assertContains($expected['permission'], $route->gatherMiddleware());
        }
    }

    public function test_las_rutas_de_especialidades_del_proveedor_conservan_sus_controladores(): void
    {
        $this->assertSame(
            'App\\Http\\Controllers\\Proveedor\\EspecialidadController@index',
            Route::getRoutes()->getByName('mi-perfil-proveedor.especialidades.index')?->getActionName()
        );
        $this->assertSame(
            'App\\Http\\Controllers\\Admin\\ProveedorEspecialidadController@index',
            Route::getRoutes()->getByName('proveedor-especialidades.index')?->getActionName()
        );
    }

    public function test_un_invitado_es_redirigido_al_login(): void
    {
        $this->get(route('especialidades.index'))
            ->assertRedirect(route('login'));
    }

    public function test_un_usuario_sin_permisos_no_puede_acceder_a_ninguna_operacion(): void
    {
        $user = User::factory()->create();
        $relacion = $this->createRubroTipoServicio();
        $especialidad = $this->createEspecialidad($relacion);

        $responses = [
            $this->actingAs($user)->get(route('especialidades.index')),
            $this->actingAs($user)->get(route('especialidades.create')),
            $this->actingAs($user)->post(route('especialidades.store'), $this->validPayload($relacion)),
            $this->actingAs($user)->get(route('especialidades.edit', $especialidad)),
            $this->actingAs($user)->put(route('especialidades.update', $especialidad), $this->validPayload($relacion, [
                'nombre' => 'Especialidad actualizada',
            ])),
            $this->actingAs($user)->delete(route('especialidades.destroy', $especialidad)),
        ];

        foreach ($responses as $response) {
            $response->assertForbidden();
        }

        $this->assertDatabaseHas('especialidades', [
            'id' => $especialidad->id,
            'nombre' => 'Especialidad inicial',
            'estado' => true,
        ]);
    }

    public function test_un_usuario_con_permiso_puede_ver_el_listado_y_sus_relaciones(): void
    {
        $user = $this->userWithPermission('ver especialidades');
        $relacion = $this->createRubroTipoServicio('Computadoras', 'Reparacion');
        $this->createEspecialidad($relacion, [
            'nombre' => 'Reparacion de laptops',
            'descripcion' => 'Diagnostico especializado.',
        ]);

        $this->actingAs($user)
            ->get(route('especialidades.index'))
            ->assertOk()
            ->assertViewIs('catalogo-servicios.especialidades.index')
            ->assertSee('Reparacion de laptops')
            ->assertSee('Computadoras')
            ->assertSee('Reparacion');
    }

    public function test_el_formulario_de_creacion_muestra_solo_relaciones_activas_con_catalogos_activos(): void
    {
        $user = $this->userWithPermission('crear especialidades');
        $elegible = $this->createRubroTipoServicio('Computadoras', 'Reparacion');
        $relacionInactiva = $this->createRubroTipoServicio('Redes', 'Instalacion', false);
        $rubroInactivo = $this->createRubroTipoServicio('Celulares', 'Mantenimiento');
        $rubroInactivo->rubro()->update(['estado' => false]);
        $tipoInactivo = $this->createRubroTipoServicio('Impresoras', 'Configuracion');
        $tipoInactivo->tipoServicio()->update(['estado' => false]);

        $this->actingAs($user)
            ->get(route('especialidades.create'))
            ->assertOk()
            ->assertViewIs('catalogo-servicios.especialidades.create')
            ->assertViewHas('rubrosTiposServicio', fn ($relaciones) => $relaciones->pluck('id')->all() === [$elegible->id]);

        $this->assertFalse($relacionInactiva->estado);
    }

    public function test_crea_una_especialidad_relacionada_con_rubro_y_tipo_de_servicio(): void
    {
        $user = $this->userWithPermission('crear especialidades');
        $relacion = $this->createRubroTipoServicio();

        $this->actingAs($user)
            ->post(route('especialidades.store'), $this->validPayload($relacion, [
                'nombre' => 'Cambio de pantalla',
                'descripcion' => 'Reemplazo de pantallas dañadas.',
                'estado' => '1',
            ]))
            ->assertRedirect(route('especialidades.index'))
            ->assertSessionHas('success', 'Especialidad creada correctamente.');

        $this->assertDatabaseHas('especialidades', [
            'rubro_tipo_servicio_id' => $relacion->id,
            'nombre' => 'Cambio de pantalla',
            'descripcion' => 'Reemplazo de pantallas dañadas.',
            'imagen' => null,
            'estado' => true,
        ]);
    }

    public function test_la_creacion_valida_campos_obligatorios_relacion_existente_y_nombre_unico_por_relacion(): void
    {
        $user = $this->userWithPermission('crear especialidades');
        $relacion = $this->createRubroTipoServicio();
        $this->createEspecialidad($relacion, ['nombre' => 'Diagnostico']);

        $this->actingAs($user)
            ->from(route('especialidades.create'))
            ->post(route('especialidades.store'), [
                'rubro_tipo_servicio_id' => 999999,
                'nombre' => '',
                'descripcion' => str_repeat('a', 1001),
            ])
            ->assertRedirect(route('especialidades.create'))
            ->assertSessionHasErrors(['rubro_tipo_servicio_id', 'nombre', 'descripcion', 'estado']);

        $this->actingAs($user)
            ->from(route('especialidades.create'))
            ->post(route('especialidades.store'), $this->validPayload($relacion, [
                'nombre' => 'Diagnostico',
            ]))
            ->assertRedirect(route('especialidades.create'))
            ->assertSessionHasErrors(['nombre']);

        $this->assertSame(1, Especialidad::query()->count());
    }

    public function test_el_mismo_nombre_se_admite_en_una_relacion_de_catalogo_distinta(): void
    {
        $user = $this->userWithPermission('crear especialidades');
        $primeraRelacion = $this->createRubroTipoServicio('Computadoras', 'Reparacion');
        $segundaRelacion = $this->createRubroTipoServicio('Celulares', 'Reparacion movil');
        $this->createEspecialidad($primeraRelacion, ['nombre' => 'Diagnostico']);

        $this->actingAs($user)
            ->post(route('especialidades.store'), $this->validPayload($segundaRelacion, [
                'nombre' => 'Diagnostico',
            ]))
            ->assertRedirect(route('especialidades.index'));

        $this->assertDatabaseHas('especialidades', [
            'rubro_tipo_servicio_id' => $segundaRelacion->id,
            'nombre' => 'Diagnostico',
        ]);
    }

    public function test_el_formulario_de_edicion_carga_la_especialidad_y_su_relacion(): void
    {
        $user = $this->userWithPermission('editar especialidades');
        $relacion = $this->createRubroTipoServicio();
        $especialidad = $this->createEspecialidad($relacion);

        $this->actingAs($user)
            ->get(route('especialidades.edit', $especialidad))
            ->assertOk()
            ->assertViewIs('catalogo-servicios.especialidades.edit')
            ->assertViewHas('especialidad', fn (Especialidad $viewEspecialidad) => $viewEspecialidad->is($especialidad))
            ->assertViewHas('rubrosTiposServicio', fn ($relaciones) => $relaciones->contains($relacion));
    }

    public function test_actualiza_una_especialidad_sin_modificar_su_estado_y_cambia_su_relacion(): void
    {
        $user = $this->userWithPermission('editar especialidades');
        $relacionAnterior = $this->createRubroTipoServicio('Computadoras', 'Reparacion');
        $relacionNueva = $this->createRubroTipoServicio('Redes', 'Instalacion');
        $especialidad = $this->createEspecialidad($relacionAnterior, ['estado' => false]);

        $this->actingAs($user)
            ->put(route('especialidades.update', $especialidad), [
                'rubro_tipo_servicio_id' => $relacionNueva->id,
                'nombre' => 'Cableado estructurado',
                'descripcion' => 'Instalacion de cableado de red.',
            ])
            ->assertRedirect(route('especialidades.index'))
            ->assertSessionHas('success', 'Especialidad actualizada correctamente.');

        $this->assertDatabaseHas('especialidades', [
            'id' => $especialidad->id,
            'rubro_tipo_servicio_id' => $relacionNueva->id,
            'nombre' => 'Cableado estructurado',
            'descripcion' => 'Instalacion de cableado de red.',
            'estado' => false,
        ]);
    }

    public function test_la_actualizacion_conserva_el_nombre_propio_y_rechaza_un_duplicado_en_la_misma_relacion(): void
    {
        $user = $this->userWithPermission('editar especialidades');
        $relacion = $this->createRubroTipoServicio();
        $especialidad = $this->createEspecialidad($relacion, ['nombre' => 'Diagnostico']);

        $this->actingAs($user)
            ->put(route('especialidades.update', $especialidad), [
                'rubro_tipo_servicio_id' => $relacion->id,
                'nombre' => 'Diagnostico',
                'descripcion' => 'Descripcion modificada.',
            ])
            ->assertRedirect(route('especialidades.index'));

        $otraEspecialidad = $this->createEspecialidad($relacion, ['nombre' => 'Mantenimiento']);

        $this->actingAs($user)
            ->from(route('especialidades.edit', $otraEspecialidad))
            ->put(route('especialidades.update', $otraEspecialidad), [
                'rubro_tipo_servicio_id' => $relacion->id,
                'nombre' => 'Diagnostico',
                'descripcion' => 'No debe persistirse.',
            ])
            ->assertRedirect(route('especialidades.edit', $otraEspecialidad))
            ->assertSessionHasErrors(['nombre']);

        $this->assertDatabaseHas('especialidades', [
            'id' => $otraEspecialidad->id,
            'nombre' => 'Mantenimiento',
        ]);
    }

    public function test_destroy_realiza_un_cambio_de_estado_reversible_sin_eliminar_el_registro(): void
    {
        $user = $this->userWithPermission('eliminar especialidades');
        $relacion = $this->createRubroTipoServicio();
        $especialidad = $this->createEspecialidad($relacion, ['estado' => true]);

        $this->actingAs($user)
            ->delete(route('especialidades.destroy', $especialidad))
            ->assertRedirect(route('especialidades.index'))
            ->assertSessionHas('success', 'Especialidad inactivada correctamente.');

        $this->assertDatabaseHas('especialidades', [
            'id' => $especialidad->id,
            'estado' => false,
        ]);

        $this->actingAs($user)
            ->delete(route('especialidades.destroy', $especialidad))
            ->assertRedirect(route('especialidades.index'))
            ->assertSessionHas('success', 'Especialidad activada correctamente.');

        $this->assertDatabaseHas('especialidades', [
            'id' => $especialidad->id,
            'estado' => true,
        ]);
    }

    public function test_livewire_protege_y_ejecuta_el_cambio_de_estado_con_el_mismo_permiso(): void
    {
        $relacion = $this->createRubroTipoServicio();
        $especialidad = $this->createEspecialidad($relacion, ['estado' => true]);
        $usuarioSinPermiso = User::factory()->create();

        Livewire::actingAs($usuarioSinPermiso)
            ->test('especialidades.tabla-especialidades')
            ->call('toggleEstado', $especialidad->id)
            ->assertForbidden();

        $usuarioAutorizado = $this->userWithPermission('eliminar especialidades');

        Livewire::actingAs($usuarioAutorizado)
            ->test('especialidades.tabla-especialidades')
            ->call('toggleEstado', $especialidad->id)
            ->assertDispatched('especialidad-estado-cambiado');

        $this->assertDatabaseHas('especialidades', [
            'id' => $especialidad->id,
            'estado' => false,
        ]);
    }

    public function test_livewire_conserva_busqueda_filtro_ordenamiento_y_tamano_de_pagina(): void
    {
        $relacion = $this->createRubroTipoServicio('Computadoras', 'Reparacion');
        $this->createEspecialidad($relacion, [
            'nombre' => 'Diagnostico visible',
            'estado' => true,
        ]);
        $this->createEspecialidad($relacion, [
            'nombre' => 'Mantenimiento inactivo',
            'estado' => false,
        ]);

        Livewire::test('especialidades.tabla-especialidades')
            ->set('search', 'Diagnostico')
            ->assertSee('Diagnostico visible')
            ->assertDontSee('Mantenimiento inactivo')
            ->set('search', '')
            ->set('estado', '0')
            ->assertSee('Mantenimiento inactivo')
            ->assertDontSee('Diagnostico visible')
            ->call('sortBy', 'nombre')
            ->assertSet('sortField', 'nombre')
            ->assertSet('sortDirection', 'asc')
            ->set('perPage', 25)
            ->assertSet('perPage', 25);
    }

    private function userWithPermission(string $permission): User
    {
        $user = User::factory()->create();
        $permissionModel = Permission::findOrCreate($permission, 'web');

        $user->givePermissionTo($permissionModel);

        return $user;
    }

    private function createRubroTipoServicio(
        string $rubroNombre = 'Rubro inicial',
        string $tipoNombre = 'Tipo inicial',
        bool $estado = true,
    ): RubroTipoServicio {
        $rubro = Rubro::query()->create([
            'nombre' => $rubroNombre,
            'descripcion' => 'Descripcion de rubro.',
            'imagen' => null,
            'estado' => true,
        ]);
        $tipoServicio = TipoServicio::query()->create([
            'nombre' => $tipoNombre,
            'descripcion' => 'Descripcion de tipo.',
            'imagen' => null,
            'estado' => true,
        ]);

        return RubroTipoServicio::query()->create([
            'rubro_id' => $rubro->id,
            'tipo_servicio_id' => $tipoServicio->id,
            'estado' => $estado,
        ]);
    }

    private function createEspecialidad(RubroTipoServicio $relacion, array $attributes = []): Especialidad
    {
        return Especialidad::query()->create(array_merge([
            'rubro_tipo_servicio_id' => $relacion->id,
            'nombre' => 'Especialidad inicial',
            'descripcion' => 'Descripcion inicial.',
            'imagen' => null,
            'estado' => true,
        ], $attributes));
    }

    private function validPayload(RubroTipoServicio $relacion, array $overrides = []): array
    {
        return array_merge([
            'rubro_tipo_servicio_id' => $relacion->id,
            'nombre' => 'Especialidad valida',
            'descripcion' => 'Descripcion valida.',
            'estado' => '1',
        ], $overrides);
    }
}
