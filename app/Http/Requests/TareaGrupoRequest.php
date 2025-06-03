<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TareaGrupoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'descripcion'  => 'required|string|min:3|max:255',
            'tarea'        => 'required|string|min:3|max:255',
            'fecha_inicio' => 'required|date|after_or_equal:today',
            'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',
            'cliente_id'   => 'required|exists:clientes,id',
            'user_id'      => 'required',
            'user_id.*'    => 'exists:users,id',
            'horas'        => 'required|numeric|min:1|max:10',
            'dias'         => 'required|array',
            'observacion'  => 'nullable|string|max:255',
        ];
    }
}
