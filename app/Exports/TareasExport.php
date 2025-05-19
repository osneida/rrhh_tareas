<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TareasExport implements FromCollection, WithHeadings, WithMapping
{

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->tarea,
            $row->estatus,
            $row->fecha,
            $row->horas,
            $row->user_id,
            $row->user->name ?? '',
            $row->cliente_id,
            $row->cliente->name ?? '',
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tarea',
            'Estatus',
            'Fecha realización',
            'Horas',
            'Empleado_id',
            'Nombre Empleado',
            'Cliente_id',
            'Nombre Cliente',

        ];
    }
}
