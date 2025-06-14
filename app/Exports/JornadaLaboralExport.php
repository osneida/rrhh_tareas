<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;

class JornadaLaboralExport implements FromCollection, WithHeadings, WithMapping, WithStyles
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
            $row->tarea->user->id ?? '',
            $row->tarea->user->name ?? '',
            $row->fecha,
            $row->hora_inicio,
            $row->hora_fin,
            $row->tarea->cliente->id,
            $row->tarea->cliente->name ?? '',
            $row->tarea->tarea ?? '',

        ];
    }
    public function headings(): array
    {
        return [
            'ID',
            'Empleado ID',
            'Nombre Empleado',
            'Fecha',
            'Hora Inicio',
            'Hora Fin',
            'Cliente ID',
            'Nombre Cliente',
            'Tarea',
        ];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(40);
        $sheet->getColumnDimension('I')->setWidth(80);
    }
}
