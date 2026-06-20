<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArregloTercero extends Model
{
    protected $table = 'arreglos_terceros';

    protected $fillable = [
        'orden_id', 'proveedor_id', 'descripcion',
        'fecha_llevado', 'fecha_recibido', 'importe',
    ];

    protected $casts = [
        'fecha_llevado'  => 'date',
        'fecha_recibido' => 'date',
        'importe'        => 'decimal:2',
    ];

    public function orden(): BelongsTo
    {
        return $this->belongsTo(Orden::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }
}
