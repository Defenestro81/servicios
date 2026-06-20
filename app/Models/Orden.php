<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Orden extends Model
{
    use SoftDeletes;

    protected $table = 'ordenes';

    protected $fillable = [
        'equipo_id', 'cliente_id', 'estado_id',
        'fecha_ingreso', 'fecha_terminado', 'fecha_retirado',
        'trabajo_solicitado', 'accesorios', 'trabajo_realizado', 'detalles',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'fecha_ingreso'   => 'date',
        'fecha_terminado' => 'date',
        'fecha_retirado'  => 'date',
    ];

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class);
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function actualizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function tecnicos(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'orden_tecnico')
            ->withPivot('principal')
            ->withTimestamps();
    }

    public function tecnicoPrincipal(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'orden_tecnico')
            ->wherePivot('principal', true)
            ->withTimestamps();
    }

    public function historialEstados(): HasMany
    {
        return $this->hasMany(OrdenEstadoHistorial::class)->orderBy('created_at', 'desc');
    }

    public function adjuntos(): HasMany
    {
        return $this->hasMany(Adjunto::class);
    }

    public function arreglosTerceros(): HasMany
    {
        return $this->hasMany(ArregloTercero::class);
    }

    public function estaAsignada(): bool
    {
        return $this->tecnicos()->exists();
    }

    public function puedeEditarTecnico(User $user): bool
    {
        if ($user->hasRole('administrador')) {
            return true;
        }
        if ($user->hasRole('tecnico')) {
            // puede editar si está asignado o si nadie la tomó
            return $this->tecnicos()->where('users.id', $user->id)->exists()
                || !$this->estaAsignada();
        }
        return false;
    }
}
