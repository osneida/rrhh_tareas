<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\JornadaLaboral;
use Livewire\WithPagination;
use App\Enums\PaginacionEnum;
use App\Exports\HorasTrabajadasExport;
use App\Livewire\Trait\FuncionesTrait;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
class HorasTrabajadas extends Component
{
    use WithPagination, FuncionesTrait;

    public $filtroEmpleado = '';
    public $allEmpleados = [];

    public function render()
    {
        $query = $this->buildQuery();
        $tiempo_transcurrido = $query->paginate($this->perPage);

        return view('livewire.admin.horas-trabajadas', compact('tiempo_transcurrido'));
    }

    public function mount()
    {
        $this->ordenCampo = 'mes';
        $this->allEmpleados = User::select('id', 'name')->orderBy('name')->get();
        $this->isAdmin = Auth::user() && Auth::user()->role === 'admin';

        $this->paginacion = PaginacionEnum::cases();
        $this->perPage = PaginacionEnum::Diez->value; // Default pagination value
    }

    private function buildQuery()
    {
        $query = JornadaLaboral::select(
            'tareas.user_id',
            'users.name as user_name',
            DB::raw('MIN(jornada_laborals.id) as id'),
            DB::raw('SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(jornada_laborals.hora_fin, jornada_laborals.hora_inicio)))) as tiempo_transcurrido'),
            DB::raw("CONCAT_WS('-',MONTH(jornada_laborals.fecha),YEAR(jornada_laborals.fecha)) as mes")
        )
            ->join('tareas', 'jornada_laborals.tarea_id', '=', 'tareas.id')
            ->join('users', 'tareas.user_id', '=', 'users.id')
            ->groupBy('tareas.user_id', 'mes', 'users.name');

        // Búsqueda
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('jornada_laborals.fecha', 'like', '%' . $this->search . '%');
            });
        }

        // Filtros
        if ($this->filtroEmpleado) {
            $query->where('tareas.user_id', $this->filtroEmpleado);
        }

        // Ordenamiento
        if ($this->ordenCampo === 'user_name') {
            $query->orderBy('users.name', $this->ordenDireccion);
        } elseif ($this->ordenCampo === 'total_horas') {
            $query->orderBy('tiempo_transcurrido', $this->ordenDireccion);
        } elseif ($this->ordenCampo === 'id') {
            $query->orderBy('id', $this->ordenDireccion);
        } else {
            $query->orderBy($this->ordenCampo, $this->ordenDireccion);
        }

        return $query;
    }

    public function exportExcel()
    {
        $fileName = 'horastrabajadas_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        $query = $this->buildQuery();
        $data = $query->get(); // Reutilizar la lógica de la consulta y obtener los datos

        return Excel::download(
            new HorasTrabajadasExport($data),
            $fileName
        );
    }
}
