<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Producto;
use App\Models\Receta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Pruebas del flujo de recetas: registro con archivo, validaciones,
 * aprobación (solo Químico Farmacéutico) y rechazo con motivo. Informe N3.
 */
class RecetaFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $quimico;
    private User $tecnico;
    private Paciente $paciente;
    private Medico $medico;
    private Producto $producto;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $gestionar = Permission::findOrCreate('gestionar_recetas', 'web');
        $validar = Permission::findOrCreate('validar_recetas', 'web');

        $rolQuimico = Role::findOrCreate('Químico Farmacéutico', 'web');
        $rolQuimico->givePermissionTo([$gestionar, $validar]);
        $rolTecnico = Role::findOrCreate('Técnico Farmacéutico', 'web');
        $rolTecnico->givePermissionTo([$gestionar, $validar]);

        $this->quimico = User::factory()->create();
        $this->quimico->assignRole($rolQuimico);
        $this->tecnico = User::factory()->create();
        $this->tecnico->assignRole($rolTecnico);

        $this->paciente = Paciente::forceCreate([
            'rut' => '11111111-1',
            'nombres' => 'Ana',
            'apellidos' => 'Pérez',
            'activo' => true,
        ]);
        $this->medico = Medico::forceCreate([
            'rut' => '22222222-2',
            'nombres' => 'Luis',
            'apellidos' => 'Soto',
            'activo' => true,
        ]);

        $categoria = Categoria::create(['nombre' => 'Prueba', 'activo' => true]);
        $this->producto = Producto::forceCreate([
            'codigo_interno' => 'RECETA-001',
            'nombre' => 'Medicamento con receta simple',
            'precio_base' => 1000,
            'categoria_id' => $categoria->id,
            'unidad_medida' => 'unidad',
            'condicion_almacenamiento' => 'ambiente',
            'tipo_receta' => 'simple',
            'requiere_receta' => true,
            'es_controlado' => false,
            'stock_minimo' => 1,
            'stock_critico' => 0,
            'activo' => true,
        ]);
    }

    private function datosReceta(array $cambios = []): array
    {
        return array_merge([
            'paciente_id' => $this->paciente->id,
            'medico_id' => $this->medico->id,
            'fecha_emision' => today()->toDateString(),
            'fecha_vencimiento' => today()->addDays(30)->toDateString(),
            'tipo' => 'simple',
            'archivo' => UploadedFile::fake()->create('receta.pdf', 100, 'application/pdf'),
            'detalles' => [
                ['producto_id' => $this->producto->id, 'cantidad_prescrita' => 3],
            ],
        ], $cambios);
    }

    private function registrarReceta(array $cambios = []): Receta
    {
        $this->actingAs($this->tecnico)
            ->post(route('recetas.store'), $this->datosReceta($cambios))
            ->assertSessionHasNoErrors();

        return Receta::firstOrFail();
    }

    public function test_registrar_receta_valida_queda_pendiente_con_archivo_y_detalle(): void
    {
        $receta = $this->registrarReceta();

        $this->assertSame('pendiente', $receta->estado);
        Storage::disk('local')->assertExists($receta->archivo_path);
        $this->assertSame(1, $receta->detalles()->count());
        $this->assertSame(3, (int) $receta->detalles()->first()->cantidad_prescrita);
    }

    public function test_archivo_con_extension_no_permitida_es_rechazado(): void
    {
        $this->actingAs($this->tecnico)
            ->post(route('recetas.store'), $this->datosReceta([
                'archivo' => UploadedFile::fake()->create('receta.exe', 100, 'application/octet-stream'),
            ]))
            ->assertSessionHasErrors('archivo');

        $this->assertSame(0, Receta::count());
    }

    public function test_receta_ya_vencida_es_rechazada(): void
    {
        $this->actingAs($this->tecnico)
            ->post(route('recetas.store'), $this->datosReceta([
                'fecha_emision' => today()->subDays(40)->toDateString(),
                'fecha_vencimiento' => today()->subDay()->toDateString(),
            ]))
            ->assertSessionHasErrors('fecha_vencimiento');

        $this->assertSame(0, Receta::count());
    }

    public function test_receta_sin_medicamentos_es_rechazada(): void
    {
        $this->actingAs($this->tecnico)
            ->post(route('recetas.store'), $this->datosReceta(['detalles' => []]))
            ->assertSessionHasErrors('detalles');

        $this->assertSame(0, Receta::count());
    }

    public function test_tipo_de_receta_que_no_corresponde_al_producto_es_rechazado(): void
    {
        $this->actingAs($this->tecnico)
            ->post(route('recetas.store'), $this->datosReceta(['tipo' => 'retenida']))
            ->assertSessionHasErrors('detalles');

        $this->assertSame(0, Receta::count());
    }

    public function test_quimico_aprueba_la_receta_y_queda_vigente(): void
    {
        $receta = $this->registrarReceta();

        $this->actingAs($this->quimico)
            ->post(route('recetas.aprobar', $receta))
            ->assertSessionHasNoErrors();

        $this->assertSame('vigente', $receta->fresh()->estado);
    }

    public function test_tecnico_no_puede_aprobar_recetas(): void
    {
        $receta = $this->registrarReceta();

        $this->actingAs($this->tecnico)
            ->post(route('recetas.aprobar', $receta))
            ->assertForbidden();

        $this->assertSame('pendiente', $receta->fresh()->estado);
    }

    public function test_rechazar_exige_motivo_y_con_motivo_queda_rechazada(): void
    {
        $receta = $this->registrarReceta();

        $this->actingAs($this->quimico)
            ->post(route('recetas.rechazar', $receta), [])
            ->assertSessionHasErrors('motivo');
        $this->assertSame('pendiente', $receta->fresh()->estado);

        $this->actingAs($this->quimico)
            ->post(route('recetas.rechazar', $receta), ['motivo' => 'Letra ilegible'])
            ->assertSessionHasNoErrors();
        $this->assertSame('rechazada', $receta->fresh()->estado);
    }

    public function test_receta_ya_revisada_no_puede_revisarse_de_nuevo(): void
    {
        $receta = $this->registrarReceta();

        $this->actingAs($this->quimico)->post(route('recetas.aprobar', $receta));
        $this->actingAs($this->quimico)
            ->post(route('recetas.rechazar', $receta), ['motivo' => 'Cambio de criterio'])
            ->assertSessionHasErrors('receta');

        $this->assertSame('vigente', $receta->fresh()->estado);
    }

    public function test_receta_con_archivo_inexistente_no_puede_aprobarse(): void
    {
        $receta = $this->registrarReceta();
        Storage::disk('local')->delete($receta->archivo_path);

        $this->actingAs($this->quimico)
            ->post(route('recetas.aprobar', $receta))
            ->assertSessionHasErrors('receta');

        $this->assertSame('pendiente', $receta->fresh()->estado);
    }
}
