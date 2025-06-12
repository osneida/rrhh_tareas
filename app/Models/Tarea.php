<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Cliente;
use App\Models\User;
use App\Models\JornadaLaboral;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tarea extends Model
{
    use HasFactory;

    protected $fillable = [
        'tarea',
        'estatus',
        'fecha',
        'user_id',
        'cliente_id',
        'horas',
        'observacion',
        'grupo_tarea_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function grupo(): HasMany
    {
        return $this->hasMany(GrupoTarea::class);
    }

    public static function horasIniciada()
    {
        return Tarea::where('estatus', 'Iniciada')->sum('horas');
    }

    public static function horasPendientes()
    {
        return Tarea::where('estatus', 'Pendiente')->sum('horas');
    }

    public static function horasCompletada()
    {
        return Tarea::where('estatus', 'Finalizada')->sum('horas');
    }

    public static function total_tareas()
    {
        return Tarea::count();
    }

    public function jornada_sintarea()
    {
        return $this->hasOne(JornadaLaboral::class, 'tarea_id')->withDefault([
            'hora_inicio' => null,
            'hora_fin' => null,
            'tarea_id' => null,
        ]);
    }
}
