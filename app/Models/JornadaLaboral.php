<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JornadaLaboral extends Model
{
    use HasFactory;

    protected $fillable = [
        'fecha',
        'hora_inicio',
        'hora_fin',
        'tarea_id'
    ];

    public function tarea(): HasOne
    {
        return $this->hasOne(Tarea::class);
    }
}



