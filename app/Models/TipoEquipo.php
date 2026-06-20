<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoEquipo extends Model
{
    protected $table = 'tipos_equipos';

    protected $fillable = ['descripcion', 'notas', 'activo'];

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function equipos(): HasMany
    {
        return $this->hasMany(Equipo::class);
    }
}
