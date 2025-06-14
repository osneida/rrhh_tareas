<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JornadaLaboralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hora_inicio' => [
                'required',
                'regex:/^(?:2[0-3]|[01][0-9]):[0-5][0-9]:[0-5][0-9]$/',
                'before:hora_fin'
            ],
            'hora_fin' => [
                'required',
                'regex:/^(?:2[0-3]|[01][0-9]):[0-5][0-9]:[0-5][0-9]$/',
                'after:hora_inicio'
            ],
        ];
    }
    public function messages()
    {
        return [
            'hora_inicio.regex' => 'El formato de la hora de inicio debe ser HH:MM:SS.',
            'hora_fin.regex' => 'El formato de la hora de fin debe ser HH:MM:SS.',
            'hora_inicio.before' => 'La hora de inicio debe ser menor que la hora de fin.',
            'hora_fin.after' => 'La hora de fin debe ser mayor que la hora de inicio.',
        ];
    }
}
