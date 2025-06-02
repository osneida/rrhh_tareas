<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Tarea;

class GrupoTarea extends Model
{
    protected $fillable = ['descripcion'];

    public function tareas(): HasMany
    {
        return $this->hasMany(Tarea::class);
    }
}
