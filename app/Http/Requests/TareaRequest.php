<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TareaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tarea'      => 'required|string|min:3|max:255',
            'fecha'      => 'required|date|after_or_equal:today',
            'cliente_id' => 'required|exists:clientes,id',
            'horas'      => 'required|numeric|min:1|max:10',
            'user_id'    => 'nullable|sometimes|exists:users,id',
            'estatus'    => 'nullable|string|in:Pendiente,Iniciada,Completada',
        ];
    }


}
