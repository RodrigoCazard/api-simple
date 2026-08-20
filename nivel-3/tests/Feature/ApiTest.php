<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas de integración: simulan pedidos HTTP y usan una base SQLite temporal.
 * RefreshDatabase crea las tablas desde cero para que cada prueba sea aislada.
 */
class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_la_raiz_en_desarrollo_muestra_la_advertencia_sobre_ia(): void
    {
        $this->getJson('/')
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonStructure(['datos' => ['aviso_ia', 'aprendizaje', 'endpoints']]);
    }

    public function test_la_raiz_en_produccion_oculta_endpoints_y_cuentas_de_prueba(): void
    {
        // Sobrescribe el entorno solamente durante esta prueba.
        $this->app->detectEnvironment(fn (): string => 'production');

        $this->getJson('/')
            ->assertOk()
            ->assertJsonPath('mensaje', 'API funcionando.')
            ->assertJsonMissingPath('datos.endpoints')
            ->assertJsonMissingPath('datos.usuarios_de_prueba');
    }

    public function test_los_productos_son_publicos_pero_el_perfil_exige_sesion(): void
    {
        $this->getJson('/api/productos')
            ->assertOk()
            ->assertJsonCount(5, 'datos');

        $this->getJson('/api/perfil')->assertUnauthorized();
    }

    public function test_el_administrador_puede_ingresar_ver_el_perfil_y_salir(): void
    {
        $login = $this->withHeader('Origin', 'http://localhost:5173')
            ->postJson('/api/login', [
                'email' => 'admin@utu.edu.uy',
                'clave' => 'admin123',
            ])
            ->assertOk()
            ->assertJsonMissingPath('datos.token')
            ->assertCookie(config('session.cookie'));

        $this->assertAuthenticated('web');
        $this->assertSame('admin@utu.edu.uy', $login->json('datos.usuario.email'));

        $this->withHeader('Origin', 'http://localhost:5173')
            ->getJson('/api/perfil')
            ->assertOk()
            ->assertJsonPath('datos.email', 'admin@utu.edu.uy');

        $this->withHeader('Origin', 'http://localhost:5173')
            ->postJson('/api/logout')
            ->assertOk();

        $this->assertGuest('web');
    }

    public function test_el_administrador_autenticado_puede_completar_el_crud(): void
    {
        $this->authenticateAs('admin@utu.edu.uy');

        $created = $this->postJson('/api/productos', [
            'nombre' => 'Producto temporal Laravel',
            'descripcion' => 'Creado por una prueba automática.',
            'precio' => 100,
            'stock' => 0,
            'categoria' => 'pruebas',
        ])->assertCreated();

        $id = $created->json('datos.id');

        $this->getJson("/api/productos/{$id}")->assertOk();

        $this->putJson("/api/productos/{$id}", ['precio' => 125.50])
            ->assertOk()
            ->assertJsonPath('datos.precio', 125.5);

        $this->deleteJson("/api/productos/{$id}")
            ->assertOk();

        $this->assertDatabaseMissing('products', ['id' => $id]);
    }

    public function test_se_aplican_las_reglas_de_negocio_y_el_middleware_de_admin(): void
    {
        $product = Product::query()->where('stock', '>', 0)->firstOrFail();
        $this->authenticateAs('alumno@utu.edu.uy');

        $this->deleteJson("/api/productos/{$product->id}")
            ->assertForbidden();

        $this->authenticateAs('admin@utu.edu.uy');

        $this->postJson("/api/productos/{$product->id}/vender", ['cantidad' => 9999])
            ->assertStatus(409)
            ->assertJsonPath('ok', false);
    }

    private function authenticateAs(string $email): void
    {
        $user = User::query()->where('email', $email)->firstOrFail();
        $this->actingAs($user, 'web');
    }
}
