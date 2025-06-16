<?php

namespace App\Livewire\Admin;

use App\Enums\EstatusTareaEnum;
use App\Enums\PaginacionEnum;
use App\Exports\JornadaLaboralExport;
use App\Http\Requests\JornadaLaboralRequest;
use App\Livewire\Trait\FuncionesTrait;
use App\Models\Cliente;
use App\Models\JornadaLaboral;
use App\Models\Tarea;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class JornadasLivewire extends Component
{
    use WithPagination, FuncionesTrait;

    public $jornadas, $jornada_id, $tarea, $empleado, $cliente;
    public $fecha, $hora_inicio, $hora_fin, $tarea_id;

    public $filtroEmpleado = '';
    public $filtroCliente = '';

    public $editMode = false;
    public $allEmpleados = [];
    public $allClientes = [];


    public function render()
    {
        $query = $this->buildQuery();
        $jornadaslb = $query->paginate($this->perPage);

        return view('livewire.admin.jornadas-livewire', [
            'jornadaslb' => $jornadaslb
        ]);
    }

    public function mount()
    {
        $this->ordenCampo = 'fecha';

        $this->allEmpleados = User::select('id', 'name')->orderBy('name')->get();
        $this->allClientes  = Cliente::select('id', 'name')->orderBy('name')->get();
        $this->isAdmin = Auth::user() && Auth::user()->role === 'admin';

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
        if ($this->filtroEmpleado) {
            $query->where('tareas.user_id', $this->filtroEmpleado);
        }
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
        } elseif ($this->ordenCampo === 'tarea') {
            $query->orderBy('tareas.tarea', $this->ordenDireccion);
        } else {
            $query->orderBy('jornada_laborals.' . $this->ordenCampo, $this->ordenDireccion);
        }

        return $query;
    }


    public function edit($jornadaId)
    {
        $this->jornada_id = $jornadaId;

        $this->jornadas = $jornada = JornadaLaboral::with('tarea')
            ->where('id', $jornadaId)
            ->get();

        foreach ($jornada as $jornada) {
            $this->tarea    = $jornada->tarea->tarea;
            $this->empleado = $jornada->tarea->user->name;
            $this->cliente  = $jornada->tarea->cliente->name;
        }

        $this->fecha       = $jornada->fecha;
        $this->hora_inicio = $jornada->hora_inicio;
        $this->hora_fin    = $jornada->hora_fin;
        $this->tarea_id    = $jornada->tarea_id;

        $this->editMode = true;
    }

    public function update()
    {
        try {
            DB::beginTransaction();

            $jornada = JornadaLaboral::findOrFail($this->jornada_id);
            $this->validate($this->reglasJornada());

            $jornada->update([
                'hora_inicio' => $this->hora_inicio,
                'hora_fin'    => $this->hora_fin,
            ]);

            $tarea_update = Tarea::find($jornada->tarea_id);
            $tarea_update->update(['estatus' => EstatusTareaEnum::Finalizada->value]);


            $this->editMode = false;

            session()->flash('success',  __('Working day updated successfully'));

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error en update JornadaLaboral: ' . $th->getMessage());
            throw $th;
        }
    }

    private function reglasJornada()
    {
        return (new JornadaLaboralRequest())->rules();
    }

    public function closeModal()
    {
        $this->search = '';
        $this->filtroEmpleado = '';
        $this->filtroCliente = '';
        $this->ordenCampo = 'fecha';
        $this->ordenDireccion = 'desc';
        $this->perPage = PaginacionEnum::Diez->value; // Reset to default pagination
        $this->editMode = false;
        $this->reset(['jornada_id', 'tarea', 'empleado', 'cliente', 'fecha', 'hora_inicio', 'hora_fin', 'tarea_id']);
    }

    public function exportExcel()
    {
        $fileName = 'jornadalaboral_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        $query = $this->buildQuery();
        $data = $query->get(); // Reutilizar la lógica de la consulta y obtener los datos

        return Excel::download(
            new JornadaLaboralExport($data),
            $fileName
        );
    }


}
