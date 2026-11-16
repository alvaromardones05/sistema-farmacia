<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductStorageConditionTest extends TestCase
{
    use RefreshDatabase;

    private User $tecnico;
    private Categoria $categoria;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::findOrCreate('Técnico Farmacéutico', 'web');
        $this->tecnico = User::factory()->create();
        $this->tecnico->assignRole($role);
        $this->categoria = Categoria::create([
            'nombre' => 'Categoría de prueba',
            'activo' => true,
        ]);
    }

    public function test_storage_condition_must_be_selected_and_is_persisted_on_create_and_edit(): void
    {
        $this->actingAs($this->tecnico)
            ->get(route('productos.create'))
            ->assertOk()
            ->assertSee('Condición de almacenamiento')
            ->assertSee('Selecciona una condición')
            ->assertDontSee('<option value="ambiente" selected>', false);

        $datos = [
            'codigo_interno' => 'FRIO-TEST-01',
            'nombre' => 'Producto refrigerado de prueba',
            'categoria_id' => $this->categoria->id,
            'unidad_medida' => 'unidad',
            'tipo_receta' => 'ninguna',
            'stock_minimo' => 0,
            'stock_critico' => 0,
            'precio_base' => 100,
        ];

        $this->actingAs($this->tecnico)
            ->post(route('productos.store'), $datos)
            ->assertSessionHasErrors('condicion_almacenamiento');
        $this->assertDatabaseMissing('productos', ['codigo_interno' => 'FRIO-TEST-01']);

        $datos['condicion_almacenamiento'] = 'refrigerado';
        $this->actingAs($this->tecnico)
            ->post(route('productos.store'), $datos)
            ->assertSessionHasNoErrors();

        $producto = Producto::where('codigo_interno', 'FRIO-TEST-01')->firstOrFail();
        $this->assertSame('refrigerado', $producto->condicion_almacenamiento);

        $this->actingAs($this->tecnico)
            ->get(route('productos.edit', $producto))
            ->assertOk()
            ->assertSee('<option value="refrigerado" selected>', false);

        $actualizacion = [
            'nombre' => $producto->nombre,
            'categoria_id' => $this->categoria->id,
            'unidad_medida' => 'unidad',
            'tipo_receta' => 'ninguna',
            'stock_minimo' => 0,
            'stock_critico' => 0,
            'precio_base' => 100,
        ];

        $this->actingAs($this->tecnico)
            ->put(route('productos.update', $producto), $actualizacion)
            ->assertSessionHasErrors('condicion_almacenamiento');
        $this->assertSame('refrigerado', $producto->fresh()->condicion_almacenamiento);

        $actualizacion['condicion_almacenamiento'] = 'ambiente';
        $this->actingAs($this->tecnico)
            ->put(route('productos.update', $producto), $actualizacion)
            ->assertSessionHasNoErrors();
        $this->assertSame('ambiente', $producto->fresh()->condicion_almacenamiento);
    }
}