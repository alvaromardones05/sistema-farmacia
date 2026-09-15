<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    // Campos que pueden ser asignados mediante User::create().
    protected $fillable = [
        'rut',
        'name',
        'apellidos',
        'email',
        'telefono',
        'password',
        'numero_registro_tecnico',
        'activo',
        'must_change_password',
        'last_login_at',
    ];

    // Campos que no deben aparecer al convertir el usuario a array o JSON.
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Conversión automática de tipos de datos.
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'activo' => 'boolean',
        'must_change_password' => 'boolean',
        'last_login_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // =====================================================
    // RELACIONES
    // =====================================================

    // Relación con las órdenes de compra creadas por el usuario.
    public function ordenesCompra()
    {
        return $this->hasMany(OrdenCompra::class);
    }

    // Relación con las recepciones de mercadería.
    public function recepciones()
    {
        return $this->hasMany(RecepcionMercaderia::class);
    }

    // Relación con los movimientos de stock.
    public function movimientosStock()
    {
        return $this->hasMany(MovimientoStock::class);
    }

    // Relación con las ventas realizadas por el usuario.
    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    // Relación con las devoluciones.
    public function devoluciones()
    {
        return $this->hasMany(Devolucion::class);
    }

    // Relación con las aperturas de caja.
    public function aperturasCaja()
    {
        return $this->hasMany(AperturaCaja::class);
    }

    // Relación con los movimientos de caja.
    public function movimientosCaja()
    {
        return $this->hasMany(MovimientoCaja::class);
    }

    // Relación con los cierres de caja.
    public function cierresCaja()
    {
        return $this->hasMany(CierreCaja::class);
    }

    // Relación con los libros de control ISP.
    public function librosControlIsp()
    {
        return $this->hasMany(LibroControlEstupefaciente::class, 'usuario_isp_id');
    }

    // Relación con los descuadres de stock controlado.
    public function descuadresStock()
    {
        return $this->hasMany(DescuadreStockControlado::class);
    }

    // Relación con los registros de auditoría.
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    // Relación con los archivos subidos.
    public function archivosSubidos()
    {
        return $this->hasMany(ArchivoSubido::class);
    }

    // Relación con los reportes generados.
    public function reportesGenerados()
    {
        return $this->hasMany(ReporteGenerado::class);
    }

    // =====================================================
    // MÉTODOS DE UTILIDAD
    // =====================================================

    // Comprueba si el usuario tiene el rol de administrador.
    public function esAdministrador(): bool
    {
        return $this->hasRole('Administrador');
    }

    // Comprueba si el usuario tiene el rol de bodeguero.
    public function esBodeqguero(): bool
    {
        return $this->hasRole('Bodeguero');
    }

    // Comprueba si el usuario tiene el rol de técnico farmacéutico.
    public function esTecnicoFarmaceutico(): bool
    {
        return $this->hasRole('Técnico Farmacéutico');
    }

    // Comprueba si el usuario tiene el rol de químico farmacéutico.
    public function esQuimicoFarmaceutico(): bool
    {
        return $this->hasRole('Químico Farmacéutico');
    }

    // Actualiza la fecha y hora del último inicio de sesión.
    public function actualizarUltimoLogin(): void
    {
        $this->update([
            'last_login_at' => now(),
        ]);
    }
}