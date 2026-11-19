<?php

namespace Tests\Feature;

use App\Models\Canje;
use App\Models\Lote;
use App\Models\OrdenCompra;
use App\Models\Receta;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;


class AccesoPorRolTest extends TestCase
{
    use RefreshDatabase;

    private const ADMIN = 'Administrador';
    private const QUIMICO = 'Químico Farmacéutico';
    private const TECNICO = 'Técnico Farmacéutico';
    private const BODEGUERO = 'Bodeguero';

    private const ACTORES = [self::ADMIN, self::QUIMICO, self::TECNICO, self::BODEGUERO, 'Invitado'];

    private static bool $reporteIniciado = false;

    public static function actores(): array
    {
        return [
            'Administrador' => [self::ADMIN],
            'Químico Farmacéutico' => [self::QUIMICO],
            'Técnico Farmacéutico' => [self::TECNICO],
            'Bodeguero' => [self::BODEGUERO],
            'Invitado' => [null],
        ];
    }

    /**
     * Matriz de acceso esperada. 'roles' lista quién debe poder entrar;
     * 'invitado' indica cómo se espera que se deniegue al invitado:
     * 'login' (redirección por el grupo auth) o '403' (ruta fuera de auth).
     */
    private static function matriz(): array
    {
        $todos = [self::ADMIN, self::QUIMICO, self::TECNICO, self::BODEGUERO];

        return [
            ['id' => 'R01', 'modulo' => 'Recetas y médicos', 'metodo' => 'GET', 'uri' => '/recetas', 'roles' => [self::ADMIN, self::QUIMICO, self::TECNICO], 'invitado' => 'login'],
            ['id' => 'R02', 'modulo' => 'Aprobar / rechazar receta', 'metodo' => 'POST', 'uri' => '/recetas/1/aprobar', 'roles' => [self::QUIMICO], 'invitado' => 'login'],
            ['id' => 'R03', 'modulo' => 'Dashboard', 'metodo' => 'GET', 'uri' => '/dashboard', 'roles' => $todos, 'invitado' => 'login'],
            ['id' => 'R04', 'modulo' => 'Perfil', 'metodo' => 'GET', 'uri' => '/profile', 'roles' => $todos, 'invitado' => 'login'],
            ['id' => 'R05', 'modulo' => 'Productos', 'metodo' => 'GET', 'uri' => '/productos', 'roles' => [self::QUIMICO, self::TECNICO], 'invitado' => 'login'],
            ['id' => 'R06', 'modulo' => 'Ventas', 'metodo' => 'GET', 'uri' => '/ventas', 'roles' => [self::QUIMICO, self::TECNICO], 'invitado' => 'login'],
            ['id' => 'R07', 'modulo' => 'Usuarios', 'metodo' => 'GET', 'uri' => '/usuarios', 'roles' => [self::ADMIN], 'invitado' => '403'],
            ['id' => 'R08', 'modulo' => 'Descuadres de stock controlado', 'metodo' => 'GET', 'uri' => '/descuadres-controlados', 'roles' => [self::QUIMICO], 'invitado' => 'login'],
            ['id' => 'R09', 'modulo' => 'Convenios y coberturas', 'metodo' => 'GET', 'uri' => '/convenios', 'roles' => [self::ADMIN, self::QUIMICO], 'invitado' => 'login'],
            ['id' => 'R10', 'modulo' => 'Lotes', 'metodo' => 'GET', 'uri' => '/lotes', 'roles' => [self::QUIMICO, self::TECNICO, self::BODEGUERO], 'invitado' => '403'],
            ['id' => 'R11', 'modulo' => 'Inventario - consulta', 'metodo' => 'GET', 'uri' => '/inventario', 'roles' => [self::ADMIN, self::QUIMICO, self::TECNICO], 'invitado' => '403'],
            ['id' => 'R12', 'modulo' => 'Próximos a vencer y consulta de canjes', 'metodo' => 'GET', 'uri' => '/inventario/proximos-vencer', 'roles' => $todos, 'invitado' => '403'],
            ['id' => 'R13', 'modulo' => 'Canjes - solicitar', 'metodo' => 'GET', 'uri' => '/inventario/canjes/solicitar/1', 'roles' => [self::ADMIN, self::TECNICO, self::BODEGUERO], 'invitado' => '403'],
            ['id' => 'R14', 'modulo' => 'Canjes - aprobar / rechazar', 'metodo' => 'POST', 'uri' => '/inventario/canjes/1/aprobar', 'roles' => [self::QUIMICO], 'invitado' => '403'],
            ['id' => 'R15', 'modulo' => 'Canjes - entregas, devoluciones y cierre', 'metodo' => 'POST', 'uri' => '/inventario/canjes/1/cerrar', 'roles' => $todos, 'invitado' => '403'],
            ['id' => 'R16', 'modulo' => 'Inventario - transferir, ajustar, marcar no apto', 'metodo' => 'GET', 'uri' => '/inventario/ajustar', 'roles' => [self::ADMIN, self::TECNICO, self::BODEGUERO], 'invitado' => '403'],
            ['id' => 'R17', 'modulo' => 'Caja', 'metodo' => 'GET', 'uri' => '/caja', 'roles' => [self::ADMIN, self::TECNICO], 'invitado' => '403'],
            ['id' => 'R18', 'modulo' => 'Pacientes', 'metodo' => 'GET', 'uri' => '/pacientes', 'roles' => [self::ADMIN, self::QUIMICO, self::TECNICO], 'invitado' => '403'],
            ['id' => 'R19', 'modulo' => 'Compras y recepciones - consulta', 'metodo' => 'GET', 'uri' => '/compras', 'roles' => [self::QUIMICO, self::BODEGUERO], 'invitado' => 'login'],
            ['id' => 'R20', 'modulo' => 'Compras - crear orden', 'metodo' => 'GET', 'uri' => '/compras/crear', 'roles' => [self::QUIMICO], 'invitado' => 'login'],
            ['id' => 'R21', 'modulo' => 'Proveedores', 'metodo' => 'GET', 'uri' => '/compras/proveedores', 'roles' => [self::QUIMICO], 'invitado' => 'login'],
            ['id' => 'R22', 'modulo' => 'Recepciones - recibir mercadería', 'metodo' => 'GET', 'uri' => '/compras/1/recibir', 'roles' => [self::BODEGUERO], 'invitado' => 'login'],
        ];
    }

    #[DataProvider('actores')]
    public function test_acceso_segun_matriz_de_roles(?string $rol): void
    {
        $this->seed(DatabaseSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Los parámetros de ruta se resuelven con modelos vacíos para que el
        // middleware de autorización se evalúe antes que cualquier búsqueda en BD.
        foreach (['receta' => Receta::class, 'canje' => Canje::class, 'lote' => Lote::class, 'orden' => OrdenCompra::class] as $parametro => $modelo) {
            Route::bind($parametro, fn () => new $modelo());
        }

        $usuario = null;
        if ($rol !== null) {
            $usuario = User::factory()->create();
            $usuario->assignRole($rol);
        }

        $posicionActor = array_search($rol ?? 'Invitado', self::ACTORES, true);
        $filas = [];
        $fallos = [];

        foreach (self::matriz() as $indice => $grupo) {
            $respuesta = $usuario
                ? $this->actingAs($usuario)->call($grupo['metodo'], $grupo['uri'])
                : $this->call($grupo['metodo'], $grupo['uri']);

            $estado = $respuesta->getStatusCode();
            $destino = (string) $respuesta->headers->get('Location');
            $casoId = sprintf('PT-%03d', $indice * count(self::ACTORES) + $posicionActor + 1);

            if ($rol === null) {
                $esperado = $grupo['invitado'] === 'login' ? 'redirige a login' : '403';
                $obtenido = ($estado === 302 && str_contains($destino, '/login')) ? 'redirige a login' : (string) $estado;
                $ok = $obtenido === 'redirige a login' || in_array($estado, [401, 403], true);
                $nota = $ok && $obtenido !== $esperado ? ' (denegado, distinto al esperado)' : '';
            } else {
                $permitido = in_array($rol, $grupo['roles'], true);
                $esperado = $permitido ? 'permitido (no 403)' : '403';
                $obtenido = (string) $estado;
                $ok = $permitido ? ! in_array($estado, [401, 403], true) : $estado === 403;
                $nota = '';
            }

            $filas[] = sprintf(
                '| %s | %s %s | %s | %s %s | %s | %s | %s |',
                $casoId,
                $grupo['id'],
                $grupo['modulo'],
                $rol ?? 'Invitado',
                $grupo['metodo'],
                $grupo['uri'],
                $esperado,
                $obtenido,
                ($ok ? 'OK' : 'FALLA') . $nota
            );

            if (! $ok) {
                $fallos[] = "{$casoId} {$grupo['id']} {$grupo['metodo']} {$grupo['uri']} como " . ($rol ?? 'Invitado') . ": esperado {$esperado}, obtenido {$obtenido}";
            }
        }

        $this->registrarEvidencia($filas);

        $this->assertEmpty(
            $fallos,
            "Discrepancias de acceso para " . ($rol ?? 'Invitado') . ":\n" . implode("\n", $fallos)
        );
    }

    private function registrarEvidencia(array $filas): void
    {
        $ruta = storage_path('logs/acceso_por_rol.md');

        if (! self::$reporteIniciado) {
            file_put_contents(
                $ruta,
                "# Resultado de pruebas de acceso por rol\n\n"
                . 'Ejecución: ' . date('d-m-Y H:i') . "\n\n"
                . "| Caso | Grupo de rutas | Actor | Petición | Esperado | Obtenido | Resultado |\n"
                . "|---|---|---|---|---|---|---|\n"
            );
            self::$reporteIniciado = true;
        }

        file_put_contents($ruta, implode("\n", $filas) . "\n", FILE_APPEND);
    }
}
