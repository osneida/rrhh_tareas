<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TareaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules($tarea_id = null): array
    {
        $rules = [
            'tarea'      => 'required|string|min:3|max:255',
            'cliente_id' => 'required|exists:clientes,id',
            'horas'      => 'required|numeric|min:1|max:10',
            'user_id'    => 'nullable|exists:users,id',
            'estatus'    => 'nullable|string|in:Pendiente,Iniciada,Completada',
        ];

        // Si es creación (no hay id), la fecha debe ser mayor o igual a hoy
        if (!$tarea_id) {
            $rules['fecha'] = 'required|date|after_or_equal:today';
        } else {
            // Si es edición (hay id), la fecha puede ser cualquier valor válido
            $rules['fecha'] = 'required|date';
        }

        return $rules;
    }
}
