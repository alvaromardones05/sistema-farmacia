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

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'activo' => 'boolean',
        'must_change_password' => 'boolean',
        'last_login_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function ordenesCompra()
    {
        return $this->hasMany(OrdenCompra::class);
    }

    public function recepciones()
    {
        return $this->hasMany(RecepcionMercaderia::class);
    }

    public function movimientosStock()
    {
        return $this->hasMany(MovimientoStock::class);
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    public function devoluciones()
    {
        return $this->hasMany(Devolucion::class);
    }

    public function aperturasCaja()
    {
        return $this->hasMany(AperturaCaja::class);
    }

    public function movimientosCaja()
    {
        return $this->hasMany(MovimientoCaja::class);
    }

    public function cierresCaja()
    {
        return $this->hasMany(CierreCaja::class);
    }

    public function librosControlIsp()
    {
        return $this->hasMany(LibroControlEstupefaciente::class, 'usuario_isp_id');
    }

    public function descuadresStock()
    {
        return $this->hasMany(DescuadreStockControlado::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function archivosSubidos()
    {
        return $this->hasMany(ArchivoSubido::class);
    }

    public function reportesGenerados()
    {
        return $this->hasMany(ReporteGenerado::class);
    }

    // Métodos de utilidad
    public function esAdministrador(): bool
    {
        return $this->hasRole('Administrador');
    }

    public function esBodeqguero(): bool
    {
        return $this->hasRole('Bodeguero');
    }

    public function esTecnicoFarmaceutico(): bool
    {
        return $this->hasRole('Técnico Farmacéutico');
    }

    public function esQuimicoFarmaceutico(): bool
    {
        return $this->hasRole('Químico Farmacéutico');
    }

    public function actualizarUltimoLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }
}