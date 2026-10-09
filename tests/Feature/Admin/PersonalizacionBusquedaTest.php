<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\Admin\ConfiguracionBusquedaVisualService;
use App\Services\Admin\MotorAdministracionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PersonalizacionBusquedaTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_el_panel_muestra_la_personalizacion_solo_con_permiso(): void
    {
        $usuario = $this->usuarioConPermisos(['ver panel motor busqueda', 'ver personalizacion busqueda']);
        $administracion = Mockery::mock(MotorAdministracionService::class);
        $administracion->shouldReceive('identificadorActivo')->once()->andReturnNull();
        $this->app->instance(MotorAdministracionService::class, $administracion);

        $this->actingAs($usuario)
            ->get(route('motor-busqueda.index'))
            ->assertOk()
            ->assertSee('Personalización de búsqueda')
            ->assertSee('Vista previa interactiva')
            ->assertSee('Datos de demostración');
    }

    public function test_guarda_y_restaura_una_configuracion_global_validada(): void
    {
        $usuario = $this->usuarioConPermisos([
            'modificar personalizacion busqueda',
            'restaurar personalizacion busqueda',
        ]);
        $servicio = app(ConfiguracionBusquedaVisualService::class);
        $datos = $servicio->valoresPredeterminados();
        $datos['color_primario'] = '#112233';
        $datos['animacion'] = 'puntos';
        $datos['presentacion_fases'] = 'linea';
        $datos['mensajes_fases']['reranking'] = 'Comparando técnicos de forma segura...';

        $this->actingAs($usuario)
            ->put(route('motor-busqueda.personalizacion.update'), $datos)
            ->assertRedirect(route('motor-busqueda.index'));

        $this->assertDatabaseHas('configuraciones_busqueda_visual', [
            'id' => 1,
            'color_primario' => '#112233',
            'animacion' => 'puntos',
            'presentacion_fases' => 'linea',
        ]);
        $this->assertSame('Comparando técnicos de forma segura...', $servicio->configuracion()->mensajes_fases['reranking']);

        $this->actingAs($usuario)
            ->delete(route('motor-busqueda.personalizacion.reset'))
            ->assertRedirect(route('motor-busqueda.index'));

        $restaurada = $servicio->configuracion()->refresh();
        $this->assertSame('#405189', $restaurada->color_primario);
        $this->assertSame('spinner', $restaurada->animacion);
    }

    public function test_rechaza_colores_css_y_mensajes_con_html(): void
    {
        $usuario = $this->usuarioConPermisos(['modificar personalizacion busqueda']);
        $datos = app(ConfiguracionBusquedaVisualService::class)->valoresPredeterminados();
        $datos['color_boton'] = 'red; background:url(javascript:alert(1))';
        $datos['mensajes_fases']['iniciando'] = '<script>alert(1)</script>';

        $this->actingAs($usuario)
            ->from(route('motor-busqueda.index'))
            ->put(route('motor-busqueda.personalizacion.update'), $datos)
            ->assertRedirect(route('motor-busqueda.index'))
            ->assertSessionHasErrors(['color_boton', 'mensajes_fases.iniciando']);
    }

    public function test_un_usuario_sin_permisos_no_puede_modificar_ni_restaurar(): void
    {
        $usuario = User::factory()->create();
        $datos = app(ConfiguracionBusquedaVisualService::class)->valoresPredeterminados();

        $this->actingAs($usuario)
            ->put(route('motor-busqueda.personalizacion.update'), $datos)
            ->assertForbidden();
        $this->actingAs($usuario)
            ->delete(route('motor-busqueda.personalizacion.reset'))
            ->assertForbidden();
    }

    private function usuarioConPermisos(array $permisos): User
    {
        $usuario = User::factory()->create();

        foreach ($permisos as $permiso) {
            Permission::findOrCreate($permiso, 'web');
            $usuario->givePermissionTo($permiso);
        }

        return $usuario;
    }
}
