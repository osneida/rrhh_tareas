<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\Estatus;
class TareaResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'        => $this->id,
            'tarea'     => $this->tarea,
            'estatus_id'=> $this->estatus,
            'estatus'   => Estatus::tryFrom($this->estatus)?->label() ?? 'Desconocido', // Convertir el valor numérico a etiqueta
            'fecha'     => $this->fecha,
            //'user'      => $this->user ? $this->user->name : null,
            //'cliente'   => $this->cliente ? $this->cliente->name : null,
            'horas'     => $this->horas,
            'cliente'   => $this->whenLoaded('cliente', function () {
                return [
                    'id' => $this->cliente->id,
                    'nombre' => $this->cliente->name,
                ];
            }),
            'empleado' => $this->whenLoaded('user', function () {
                return $this->user ? [
                    'id' => $this->user->id,
                    'nombre' => $this->user->name,
                ] : null;
            }),
        ];
    }

    public function with($request)
    {
        return [
            'message' => $this->additional['message'] ?? null,
        ];
    }
}
