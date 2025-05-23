<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules($clienteId = null): array
    {
        $rules = [
            'address'   => 'nullable|string|min:3|max:45',
            'status'    => 'nullable',
        ];

        // Si es creación (no hay id)
        if (!$clienteId) {
            $rules['name']  = 'required|string|min:3|max:45|unique:clientes';
            $rules['mail']  = 'nullable|email|min:8|max:30|unique:clientes';
            $rules['cif']   = 'nullable|unique:clientes';
            $rules['phone'] = 'nullable|string|min:3|max:20|unique:clientes';
        } else {
            // Si es edición (hay id)
            $rules['name']  = 'required|string|min:3|max:45|unique:clientes,name,'.$clienteId;
            $rules['mail']  = 'nullable|email|min:8|max:30|unique:clientes,mail,'.$clienteId;
            $rules['cif']   = 'nullable|unique:clientes,cif,'.$clienteId;
            $rules['phone'] = 'nullable|string|min:3|max:20|unique:clientes,phone,'.$clienteId;
        }

        return $rules;
    }
}
