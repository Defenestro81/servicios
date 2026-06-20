<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenEstadoHistorial extends Model
{
    protected $table = 'orden_estado_historial';

    protected $fillable = ['orden_id', 'estado_id', 'user_id', 'nota'];

    public function orden(): BelongsTo
    {
        return $this->belongsTo(Orden::class);
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
