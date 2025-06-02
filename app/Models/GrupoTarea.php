<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Tarea;

class GrupoTarea extends Model
{
    protected $fillable = ['descripcion','fecha_inicio', 'fecha_fin', 'dias'];
    protected $casts = [
        'dias' => 'array',
    ];

    public function tareas(): HasMany
    {
        return $this->hasMany(Tarea::class);
    }
}
