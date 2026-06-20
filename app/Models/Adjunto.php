<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Adjunto extends Model
{
    protected $fillable = ['orden_id', 'ruta', 'nombre_original', 'mime_type', 'tamano', 'descripcion', 'subido_por'];

    public function orden(): BelongsTo
    {
        return $this->belongsTo(Orden::class);
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->ruta);
    }

    public function esImagen(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }
}
