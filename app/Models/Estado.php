<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estado extends Model
{
    protected $fillable = ['nombre', 'orden'];

    public function ordenes(): HasMany
    {
        return $this->hasMany(Orden::class);
    }

    public function historial(): HasMany
    {
        return $this->hasMany(OrdenEstadoHistorial::class);
    }
}
