<?php

namespace Tests\Feature\CatalogoServicios;

use App\Models\Permission;
use App\Models\Rubro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RubroControllerCharacterizationTest extends TestCase
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
            'rubros.index' => [
                'methods' => ['GET', 'HEAD'],
                'action' => 'App\\Http\\Controllers\\CatalogoServicios\\RubroController@index',
                'permission' => 'permission:ver rubros',
            ],
            'rubros.create' => [
                'methods' => ['GET', 'HEAD'],
                'action' => 'App\\Http\\Controllers\\CatalogoServicios\\RubroController@create',
                'permission' => 'permission:crear rubros',
            ],
            'rubros.store' => [
                'methods' => ['POST'],
                'action' => 'App\\Http\\Controllers\\CatalogoServicios\\RubroController@store',
                'permission' => 'permission:crear rubros',
            ],
            'rubros.edit' => [
                'methods' => ['GET', 'HEAD'],
                'action' => 'App\\Http\\Controllers\\CatalogoServicios\\RubroController@edit',
                'permission' => 'permission:editar rubros',
            ],
            'rubros.update' => [
                'methods' => ['PUT', 'PATCH'],
                'action' => 'App\\Http\\Controllers\\CatalogoServicios\\RubroController@update',
                'permission' => 'permission:editar rubros',
            ],
            'rubros.destroy' => [
                'methods' => ['DELETE'],
                'action' => 'App\\Http\\Controllers\\CatalogoServicios\\RubroController@destroy',
                'permission' => 'permission:eliminar rubros',
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
        $this->get(route('rubros.index'))
            ->assertRedirect(route('login'));
    }

    public function test_un_usuario_sin_permisos_no_puede_acceder_a_ninguna_operacion(): void
    {
        $user = User::factory()->create();
        $rubro = $this->createRubro();

        $responses = [
            $this->actingAs($user)->get(route('rubros.index')),
            $this->actingAs($user)->get(route('rubros.create')),
            $this->actingAs($user)->post(route('rubros.store'), $this->validPayload()),
            $this->actingAs($user)->get(route('rubros.edit', $rubro)),
            $this->actingAs($user)->put(route('rubros.update', $rubro), $this->validPayload([
                'nombre' => 'Rubro actualizado',
            ])),
            $this->actingAs($user)->delete(route('rubros.destroy', $rubro)),
        ];

        foreach ($responses as $response) {
            $response->assertForbidden();
        }

        $this->assertDatabaseHas('rubros', [
            'id' => $rubro->id,
            'nombre' => 'Rubro inicial',
            'estado' => true,
        ]);
    }

    public function test_un_usuario_con_permiso_puede_ver_el_listado_y_sus_registros(): void
    {
        $user = $this->userWithPermission('ver rubros');
        $this->createRubro([
            'nombre' => 'Telefonia movil',
            'descripcion' => 'Diagnostico y reparacion de celulares.',
        ]);

        $this->actingAs($user)
            ->get(route('rubros.index'))
            ->assertOk()
            ->assertViewIs('catalogo-servicios.rubros.index')
            ->assertSee('Telefonia movil')
            ->assertSee('Diagnostico y reparacion de celulares.');
    }

    public function test_un_usuario_con_permiso_puede_abrir_el_formulario_de_creacion(): void
    {
        $user = $this->userWithPermission('crear rubros');

        $this->actingAs($user)
            ->get(route('rubros.create'))
            ->assertOk()
            ->assertViewIs('catalogo-servicios.rubros.create');
    }

    public function test_crea_un_rubro_y_redirige_al_listado(): void
    {
        $user = $this->userWithPermission('crear rubros');

        $this->actingAs($user)
            ->post(route('rubros.store'), $this->validPayload([
                'nombre' => 'Redes y conectividad',
                'descripcion' => 'Instalacion y mantenimiento de redes.',
                'estado' => '1',
            ]))
            ->assertRedirect(route('rubros.index'))
            ->assertSessionHas('success', 'Rubro creado correctamente.');

        $this->assertDatabaseHas('rubros', [
            'nombre' => 'Redes y conectividad',
            'descripcion' => 'Instalacion y mantenimiento de redes.',
            'estado' => true,
            'imagen' => null,
        ]);
    }

    public function test_la_creacion_valida_campos_obligatorios_y_nombre_unico(): void
    {
        $user = $this->userWithPermission('crear rubros');
        $this->createRubro(['nombre' => 'Computadoras']);

        $this->actingAs($user)
            ->from(route('rubros.create'))
            ->post(route('rubros.store'), [
                'nombre' => 'Computadoras',
                'descripcion' => str_repeat('a', 1001),
            ])
            ->assertRedirect(route('rubros.create'))
            ->assertSessionHasErrors(['nombre', 'descripcion', 'estado']);

        $this->assertSame(1, Rubro::query()->count());
    }

    public function test_un_usuario_con_permiso_puede_abrir_el_formulario_de_edicion(): void
    {
        $user = $this->userWithPermission('editar rubros');
        $rubro = $this->createRubro();

        $this->actingAs($user)
            ->get(route('rubros.edit', $rubro))
            ->assertOk()
            ->assertViewIs('catalogo-servicios.rubros.edit')
            ->assertViewHas('rubro', fn (Rubro $viewRubro) => $viewRubro->is($rubro));
    }

    public function test_actualiza_un_rubro_sin_modificar_su_estado(): void
    {
        $user = $this->userWithPermission('editar rubros');
        $rubro = $this->createRubro(['estado' => false]);

        $this->actingAs($user)
            ->put(route('rubros.update', $rubro), [
                'nombre' => 'Soporte de laptops',
                'descripcion' => 'Mantenimiento preventivo y correctivo.',
            ])
            ->assertRedirect(route('rubros.index'))
            ->assertSessionHas('success', 'Rubro actualizado correctamente.');

        $this->assertDatabaseHas('rubros', [
            'id' => $rubro->id,
            'nombre' => 'Soporte de laptops',
            'descripcion' => 'Mantenimiento preventivo y correctivo.',
            'estado' => false,
        ]);
    }

    public function test_la_actualizacion_permite_conservar_el_nombre_propio_y_rechaza_otro_duplicado(): void
    {
        $user = $this->userWithPermission('editar rubros');
        $rubro = $this->createRubro(['nombre' => 'Computadoras']);

        $this->actingAs($user)
            ->put(route('rubros.update', $rubro), [
                'nombre' => 'Computadoras',
                'descripcion' => 'Descripcion modificada.',
            ])
            ->assertRedirect(route('rubros.index'));

        $otroRubro = $this->createRubro(['nombre' => 'Celulares']);

        $this->actingAs($user)
            ->from(route('rubros.edit', $otroRubro))
            ->put(route('rubros.update', $otroRubro), [
                'nombre' => 'Computadoras',
                'descripcion' => 'No debe persistirse.',
            ])
            ->assertRedirect(route('rubros.edit', $otroRubro))
            ->assertSessionHasErrors(['nombre']);

        $this->assertDatabaseHas('rubros', [
            'id' => $otroRubro->id,
            'nombre' => 'Celulares',
        ]);
    }

    public function test_destroy_realiza_un_cambio_de_estado_reversible_sin_eliminar_el_registro(): void
    {
        $user = $this->userWithPermission('eliminar rubros');
        $rubro = $this->createRubro(['estado' => true]);

        $this->actingAs($user)
            ->delete(route('rubros.destroy', $rubro))
            ->assertRedirect(route('rubros.index'))
            ->assertSessionHas('success', 'Rubro inactivado correctamente.');

        $this->assertDatabaseHas('rubros', [
            'id' => $rubro->id,
            'estado' => false,
        ]);

        $this->actingAs($user)
            ->delete(route('rubros.destroy', $rubro))
            ->assertRedirect(route('rubros.index'))
            ->assertSessionHas('success', 'Rubro activado correctamente.');

        $this->assertDatabaseHas('rubros', [
            'id' => $rubro->id,
            'estado' => true,
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

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'nombre' => 'Rubro valido',
            'descripcion' => 'Descripcion valida.',
            'estado' => '1',
        ], $overrides);
    }
}
