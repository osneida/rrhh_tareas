<?php

namespace App\Livewire\Empleado;

use Livewire\Component;
use App\Enums\PaginacionEnum;
use App\Livewire\Trait\FuncionesTrait;
use App\Models\Cliente;
use App\Models\JornadaLaboral;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class MisJornadasLivewire extends Component
{
    use WithPagination, FuncionesTrait;

    public $filtroCliente = '';
    public $allClientes = [];

    public function render()
    {
        $query = $this->buildQuery();
        $jornadaslb = $query->paginate($this->perPage);

        return view('livewire.empleado.mis-jornadas-livewire', [
            'jornadaslb' => $jornadaslb
        ]);
    }

    public function mount()
    {
        $this->ordenCampo = 'fecha';
        $this->ordenDireccion = 'desc';
        $this->paginacion = PaginacionEnum::cases();
        $this->perPage = PaginacionEnum::Diez->value; // Default pagination value
    }

    private function buildQuery()
    {
        $query = JornadaLaboral::with('tarea.user', 'tarea.cliente')->select(
            'jornada_laborals.id',
            'jornada_laborals.fecha',
            'jornada_laborals.hora_inicio',
            'jornada_laborals.hora_fin',
            'jornada_laborals.tarea_id',
            DB::raw('HOUR(TIMEDIFF(hora_fin, hora_inicio)) as horas_transcurridas'),
            DB::raw('MINUTE(TIMEDIFF(hora_fin, hora_inicio)) as minutos_transcurridos')
        )
            ->join('tareas', 'jornada_laborals.tarea_id', '=', 'tareas.id');

        // Filtrar por usuario autenticado
        $query->whereHas('tarea', function ($q) {
            $q->where('user_id', Auth::id());
        });

        $clientes = $query->get();
        $this->allClientes = Cliente::select('id', 'name')->whereIn('id', $clientes->pluck('tarea.cliente_id')->unique())->orderBy('name')->get();

        // Búsqueda
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('jornada_laborals.fecha', 'like', '%' . $this->search . '%')
                    ->orWhere('jornada_laborals.hora_inicio', 'like', '%' . $this->search . '%')
                    ->orWhere('jornada_laborals.hora_fin', 'like', '%' . $this->search . '%')
                    ->orWhereHas('tarea', function ($q2) {
                        $q2->where('tarea', 'like', '%' . $this->search . '%');
                    });
            });
        }

        // Filtros

        if ($this->filtroCliente) {
            $query->where('tareas.cliente_id', $this->filtroCliente);
        }

        // Ordenamiento
        if ($this->ordenCampo === 'user_name') {
            $query->join('users', 'tareas.user_id', '=', 'users.id')
                ->orderBy('users.name', $this->ordenDireccion)
                ->select(
                    'jornada_laborals.*',
                    DB::raw('HOUR(TIMEDIFF(hora_fin, hora_inicio)) as horas_transcurridas'),
                    DB::raw('MINUTE(TIMEDIFF(hora_fin, hora_inicio)) as minutos_transcurridos')
                );
        } elseif ($this->ordenCampo === 'cliente_name') {
            $query->join('clientes', 'tareas.cliente_id', '=', 'clientes.id')
                ->orderBy('clientes.name', $this->ordenDireccion)
                ->select(
                    'jornada_laborals.*',
                    DB::raw('HOUR(TIMEDIFF(hora_fin, hora_inicio)) as horas_transcurridas'),
                    DB::raw('MINUTE(TIMEDIFF(hora_fin, hora_inicio)) as minutos_transcurridos')
                );
        } elseif ($this->ordenCampo === 'total_horas') {
            $query->select(
                'jornada_laborals.*',
                DB::raw('HOUR(TIMEDIFF(hora_fin, hora_inicio)) as horas_transcurridas'),
                DB::raw('MINUTE(TIMEDIFF(hora_fin, hora_inicio)) as minutos_transcurridos')
            )
                ->orderByRaw('HOUR(TIMEDIFF(hora_fin, hora_inicio)) ' . $this->ordenDireccion . ', MINUTE(TIMEDIFF(hora_fin, hora_inicio)) ' . $this->ordenDireccion);
        } else {
            $query->orderBy('jornada_laborals.' . $this->ordenCampo, $this->ordenDireccion);
        }

        return $query;
    }

}
