<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenTecnico extends Model
{
    protected $table = 'orden_tecnico';

    protected $fillable = ['orden_id', 'user_id', 'principal'];

    protected $casts = ['principal' => 'boolean'];

    public function orden(): BelongsTo
    {
        return $this->belongsTo(Orden::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
