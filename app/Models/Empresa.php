<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    protected $fillable = ['nombre', 'razon_social', 'cuit'];

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class);
    }
}
