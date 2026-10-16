<?php

namespace Tests\Feature\CatalogoServicios;

use App\Models\Permission;
use App\Models\Rubro;
use App\Models\TipoServicio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TipoServicioControllerCharacterizationTest extends TestCase
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
            'tipos-servicio.index' => [
                'methods' => ['GET', 'HEAD'],
                'action' => 'App\\Http\\Controllers\\CatalogoServicios\\TipoServicioController@index',
                'permission' => 'permission:ver tipos servicio',
            ],
            'tipos-servicio.create' => [
                'methods' => ['GET', 'HEAD'],
                'action' => 'App\\Http\\Controllers\\CatalogoServicios\\TipoServicioController@create',
                'permission' => 'permission:crear tipos servicio',
            ],
            'tipos-servicio.store' => [
                'methods' => ['POST'],
                'action' => 'App\\Http\\Controllers\\CatalogoServicios\\TipoServicioController@store',
                'permission' => 'permission:crear tipos servicio',
            ],
            'tipos-servicio.edit' => [
                'methods' => ['GET', 'HEAD'],
                'action' => 'App\\Http\\Controllers\\CatalogoServicios\\TipoServicioController@edit',
                'permission' => 'permission:editar tipos servicio',
            ],
            'tipos-servicio.update' => [
                'methods' => ['PUT', 'PATCH'],
                'action' => 'App\\Http\\Controllers\\CatalogoServicios\\TipoServicioController@update',
                'permission' => 'permission:editar tipos servicio',
            ],
            'tipos-servicio.destroy' => [
                'methods' => ['DELETE'],
                'action' => 'App\\Http\\Controllers\\CatalogoServicios\\TipoServicioController@destroy',
                'permission' => 'permission:eliminar tipos servicio',
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

    public function test_un_invitado_es_redirigido_al_login(): void
    {
        $this->get(route('tipos-servicio.index'))
            ->assertRedirect(route('login'));
    }

    public function test_un_usuario_sin_permisos_no_puede_acceder_a_ninguna_operacion(): void
    {
        $user = User::factory()->create();
        $rubro = $this->createRubro();
        $tipoServicio = $this->createTipoServicio($rubro);

        $responses = [
            $this->actingAs($user)->get(route('tipos-servicio.index')),
            $this->actingAs($user)->get(route('tipos-servicio.create')),
            $this->actingAs($user)->post(route('tipos-servicio.store'), $this->validPayload($rubro)),
            $this->actingAs($user)->get(route('tipos-servicio.edit', $tipoServicio)),
            $this->actingAs($user)->put(route('tipos-servicio.update', $tipoServicio), $this->validPayload($rubro, [
                'nombre' => 'Servicio actualizado',
            ])),
            $this->actingAs($user)->delete(route('tipos-servicio.destroy', $tipoServicio)),
        ];

        foreach ($responses as $response) {
            $response->assertForbidden();
        }

        $this->assertDatabaseHas('tipos_servicio', [
            'id' => $tipoServicio->id,
            'nombre' => 'Servicio inicial',
            'estado' => true,
        ]);
    }

    public function test_un_usuario_con_permiso_puede_ver_el_listado_y_sus_relaciones(): void
    {
        $user = $this->userWithPermission('ver tipos servicio');
        $rubro = $this->createRubro(['nombre' => 'Computadoras']);
        $this->createTipoServicio($rubro, [
            'nombre' => 'Reparacion de laptops',
            'descripcion' => 'Diagnostico y mantenimiento de laptops.',
        ]);

        $this->actingAs($user)
            ->get(route('tipos-servicio.index'))
            ->assertOk()
            ->assertViewIs('catalogo-servicios.tipos-servicio.index')
            ->assertSee('Reparacion de laptops')
            ->assertSee('Computadoras');
    }

    public function test_el_formulario_de_creacion_muestra_solo_rubros_activos(): void
    {
        $user = $this->userWithPermission('crear tipos servicio');
        $activo = $this->createRubro(['nombre' => 'Rubro activo']);
        $this->createRubro(['nombre' => 'Rubro inactivo', 'estado' => false]);

        $this->actingAs($user)
            ->get(route('tipos-servicio.create'))
            ->assertOk()
            ->assertViewIs('catalogo-servicios.tipos-servicio.create')
            ->assertViewHas('rubros', fn ($rubros) => $rubros->contains($activo) && $rubros->count() === 1);
    }

    public function test_crea_un_tipo_de_servicio_y_sincroniza_sus_rubros(): void
    {
        $user = $this->userWithPermission('crear tipos servicio');
        $primerRubro = $this->createRubro(['nombre' => 'Computadoras']);
        $segundoRubro = $this->createRubro(['nombre' => 'Redes']);

        $this->actingAs($user)
            ->post(route('tipos-servicio.store'), $this->validPayload($primerRubro, [
                'nombre' => 'Mantenimiento preventivo',
                'descripcion' => 'Limpieza y diagnostico.',
                'estado' => '1',
                'rubros' => [$primerRubro->id, $segundoRubro->id],
            ]))
            ->assertRedirect(route('tipos-servicio.index'))
            ->assertSessionHas('success', 'Tipo de servicio creado correctamente.');

        $tipoServicio = TipoServicio::query()->where('nombre', 'Mantenimiento preventivo')->firstOrFail();

        $this->assertDatabaseHas('tipos_servicio', [
            'id' => $tipoServicio->id,
            'descripcion' => 'Limpieza y diagnostico.',
            'estado' => true,
            'imagen' => null,
        ]);
        $this->assertSame(
            [$primerRubro->id, $segundoRubro->id],
            $tipoServicio->rubros()->orderBy('rubros.id')->pluck('rubros.id')->all()
        );
        $this->assertDatabaseHas('rubro_tipo_servicio', [
            'tipo_servicio_id' => $tipoServicio->id,
            'rubro_id' => $primerRubro->id,
            'estado' => true,
        ]);
    }

    public function test_la_creacion_valida_campos_obligatorios_nombre_unico_y_rubros_existentes(): void
    {
        $user = $this->userWithPermission('crear tipos servicio');
        $rubro = $this->createRubro();
        $this->createTipoServicio($rubro, ['nombre' => 'Diagnostico']);

        $this->actingAs($user)
            ->from(route('tipos-servicio.create'))
            ->post(route('tipos-servicio.store'), [
                'nombre' => 'Diagnostico',
                'descripcion' => str_repeat('a', 1001),
                'rubros' => [999999],
            ])
            ->assertRedirect(route('tipos-servicio.create'))
            ->assertSessionHasErrors(['nombre', 'descripcion', 'estado', 'rubros.0']);

        $this->assertSame(1, TipoServicio::query()->count());
    }

    public function test_el_formulario_de_edicion_carga_el_tipo_y_sus_rubros(): void
    {
        $user = $this->userWithPermission('editar tipos servicio');
        $rubro = $this->createRubro();
        $tipoServicio = $this->createTipoServicio($rubro);

        $this->actingAs($user)
            ->get(route('tipos-servicio.edit', $tipoServicio))
            ->assertOk()
            ->assertViewIs('catalogo-servicios.tipos-servicio.edit')
            ->assertViewHas('tipoServicio', fn (TipoServicio $viewTipo) => $viewTipo->is($tipoServicio))
            ->assertViewHas('rubros', fn ($rubros) => $rubros->contains($rubro));
    }

    public function test_actualiza_un_tipo_sin_modificar_su_estado_y_reemplaza_sus_rubros(): void
    {
        $user = $this->userWithPermission('editar tipos servicio');
        $rubroAnterior = $this->createRubro(['nombre' => 'Computadoras']);
        $rubroNuevo = $this->createRubro(['nombre' => 'Redes']);
        $tipoServicio = $this->createTipoServicio($rubroAnterior, ['estado' => false]);

        $this->actingAs($user)
            ->put(route('tipos-servicio.update', $tipoServicio), [
                'nombre' => 'Instalacion de redes',
                'descripcion' => 'Cableado y configuracion.',
                'rubros' => [$rubroNuevo->id],
            ])
            ->assertRedirect(route('tipos-servicio.index'))
            ->assertSessionHas('success', 'Tipo de servicio actualizado correctamente.');

        $this->assertDatabaseHas('tipos_servicio', [
            'id' => $tipoServicio->id,
            'nombre' => 'Instalacion de redes',
            'descripcion' => 'Cableado y configuracion.',
            'estado' => false,
        ]);
        $this->assertDatabaseMissing('rubro_tipo_servicio', [
            'tipo_servicio_id' => $tipoServicio->id,
            'rubro_id' => $rubroAnterior->id,
        ]);
        $this->assertDatabaseHas('rubro_tipo_servicio', [
            'tipo_servicio_id' => $tipoServicio->id,
            'rubro_id' => $rubroNuevo->id,
            'estado' => true,
        ]);
    }

    public function test_la_actualizacion_conserva_el_nombre_propio_y_rechaza_otro_duplicado(): void
    {
        $user = $this->userWithPermission('editar tipos servicio');
        $rubro = $this->createRubro();
        $tipoServicio = $this->createTipoServicio($rubro, ['nombre' => 'Diagnostico']);

        $this->actingAs($user)
            ->put(route('tipos-servicio.update', $tipoServicio), [
                'nombre' => 'Diagnostico',
                'descripcion' => 'Descripcion modificada.',
                'rubros' => [$rubro->id],
            ])
            ->assertRedirect(route('tipos-servicio.index'));

        $otroTipo = $this->createTipoServicio($rubro, ['nombre' => 'Mantenimiento']);

        $this->actingAs($user)
            ->from(route('tipos-servicio.edit', $otroTipo))
            ->put(route('tipos-servicio.update', $otroTipo), [
                'nombre' => 'Diagnostico',
                'descripcion' => 'No debe persistirse.',
                'rubros' => [$rubro->id],
            ])
            ->assertRedirect(route('tipos-servicio.edit', $otroTipo))
            ->assertSessionHasErrors(['nombre']);

        $this->assertDatabaseHas('tipos_servicio', [
            'id' => $otroTipo->id,
            'nombre' => 'Mantenimiento',
        ]);
    }

    public function test_destroy_realiza_un_cambio_de_estado_reversible_sin_eliminar_el_registro(): void
    {
        $user = $this->userWithPermission('eliminar tipos servicio');
        $rubro = $this->createRubro();
        $tipoServicio = $this->createTipoServicio($rubro, ['estado' => true]);

        $this->actingAs($user)
            ->delete(route('tipos-servicio.destroy', $tipoServicio))
            ->assertRedirect(route('tipos-servicio.index'))
            ->assertSessionHas('success', 'Tipo de servicio inactivado correctamente.');

        $this->assertDatabaseHas('tipos_servicio', [
            'id' => $tipoServicio->id,
            'estado' => false,
        ]);

        $this->actingAs($user)
            ->delete(route('tipos-servicio.destroy', $tipoServicio))
            ->assertRedirect(route('tipos-servicio.index'))
            ->assertSessionHas('success', 'Tipo de servicio activado correctamente.');

        $this->assertDatabaseHas('tipos_servicio', [
            'id' => $tipoServicio->id,
            'estado' => true,
        ]);
    }

    public function test_livewire_protege_y_ejecuta_el_cambio_de_estado_con_el_mismo_permiso(): void
    {
        $rubro = $this->createRubro();
        $tipoServicio = $this->createTipoServicio($rubro, ['estado' => true]);
        $usuarioSinPermiso = User::factory()->create();

        Livewire::actingAs($usuarioSinPermiso)
            ->test('tipos-servicio.tabla-tipos-servicio')
            ->call('toggleEstado', $tipoServicio->id)
            ->assertForbidden();

        $usuarioAutorizado = $this->userWithPermission('eliminar tipos servicio');

        Livewire::actingAs($usuarioAutorizado)
            ->test('tipos-servicio.tabla-tipos-servicio')
            ->call('toggleEstado', $tipoServicio->id)
            ->assertDispatched('tipo-servicio-estado-cambiado');

        $this->assertDatabaseHas('tipos_servicio', [
            'id' => $tipoServicio->id,
            'estado' => false,
        ]);
    }

    private function userWithPermission(string $permission): User
    {
        $user = User::factory()->create();
        $permissionModel = Permission::findOrCreate($permission, 'web');

        $user->givePermissionTo($permissionModel);

        return $user;
    }

    private function createRubro(array $attributes = []): Rubro
    {
        return Rubro::query()->create(array_merge([
            'nombre' => 'Rubro inicial',
            'descripcion' => 'Descripcion inicial.',
            'imagen' => null,
            'estado' => true,
        ], $attributes));
    }

    private function createTipoServicio(Rubro $rubro, array $attributes = []): TipoServicio
    {
        $tipoServicio = TipoServicio::query()->create(array_merge([
            'nombre' => 'Servicio inicial',
            'descripcion' => 'Descripcion inicial.',
            'imagen' => null,
            'estado' => true,
        ], $attributes));

        $tipoServicio->rubros()->attach($rubro->id, ['estado' => true]);

        return $tipoServicio;
    }

    private function validPayload(Rubro $rubro, array $overrides = []): array
    {
        return array_merge([
            'nombre' => 'Servicio valido',
            'descripcion' => 'Descripcion valida.',
            'estado' => '1',
            'rubros' => [$rubro->id],
        ], $overrides);
    }
}
