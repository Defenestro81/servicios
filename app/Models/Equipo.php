<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Equipo extends Model
{
    use SoftDeletes;

    protected $fillable = ['tipo_equipo_id', 'etiqueta', 'marca', 'modelo', 'nro_serie'];

    protected static function booted(): void
    {
        static::creating(function (Equipo $equipo) {
            if (empty($equipo->etiqueta)) {
                $n = (static::withTrashed()->count() + 1);
                $equipo->etiqueta = 'EQ-' . str_pad($n, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoEquipo::class, 'tipo_equipo_id');
    }

    public function ordenes(): HasMany
    {
        return $this->hasMany(Orden::class);
    }

    public function getDescripcionAttribute(): string
    {
        return implode(' ', array_filter([$this->marca, $this->modelo])) ?: '—';
    }
}
