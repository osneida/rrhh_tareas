<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Enums\PaginacionEnum;
use App\Enums\DiasEnum;
use App\Http\Requests\TareaGrupoRequest;
use App\Http\Requests\TareaRequest;
use App\Models\Cliente;
use App\Models\GrupoTarea;
use App\Models\Tarea;
use App\Models\User;
use App\Enums\EstatusTareaEnum;
use App\Livewire\Trait\FuncionesTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Livewire\Trait\TareaTrait;
use Illuminate\Support\Facades\DB;

class GrupoTareaLivewire extends Component
{

    use WithPagination, TareaTrait, FuncionesTrait;

    public $filtroEstatus = '';
    public $filtroEmpleado = '';

    public $selectEstatusTarea;
    public $allEmpleados = [];
    public $tareasEmpleados = [];
    public $allClientes = [];
    public $losDias;
    public $dias = [];

    public $editMode   = false;
    public $createMode = false;
    public $dashboard  = true;
    public $editTarea  = false;
    public $deleteMode = false;
    public $deleteGroupMode = false;

    public $showTarea  = null;

    public $descripcion, $fecha_inicio, $fecha_fin;
    public $tareas,  $tarea, $tarea_id;
    public $estatus, $fecha, $horas = 1, $user_id, $cliente_id, $cliente_name, $observacion, $grupo_tarea_id;

    protected $updatesQueryString = [
        ['search'  => ['except' => '']],
        ['perPage' => ['except' => PaginacionEnum::Diez->value]],
        ['sortField' => ['except' => 'id']],
        ['sortDirection' => ['except' => 'desc']]

    ];

    public function render()
    {
        $tareas_grupo = GrupoTarea::query()
            ->with('tareas')
            ->when(
                $this->search,
                fn($q) =>
                $q->where(function ($q) {
                    $q->where('descripcion', 'like', "%{$this->search}%")
                        ->orWhere('created_at', 'like', "%{$this->search}%");
                })
            )
            ->orderBy($this->ordenCampo, $this->ordenDireccion)
            ->paginate($this->perPage);

        $tareasPaginadas = null;
        if ($this->grupo_tarea_id && !$this->dashboard) {
            $grupo = GrupoTarea::find($this->grupo_tarea_id);
            $tareasPaginadas = $grupo
                ? $grupo->tareas()
                ->when($this->filtroEstatus, function ($query) {
                    $query->where('estatus', $this->filtroEstatus);
                })
                ->when($this->filtroEmpleado, function ($query) {
                    $query->where('user_id', $this->filtroEmpleado);
                })
                ->when($this->ordenCampo === 'user_name', function ($query) {
                    $query->join('users', 'tareas.user_id', '=', 'users.id')
                        ->select('tareas.*')
                        ->orderBy('users.name', $this->ordenDireccion);
                })
                ->when($this->ordenCampo !== 'user_name', function ($query) {
                    $query->orderBy($this->ordenCampo, $this->ordenDireccion);
                })
                ->paginate(10)
                : null;

            $this->cliente_name = $grupo->tareas->first()->cliente->name ?? null;
            $this->tareasEmpleados = User::select('id', 'name')
                ->whereIn('id', $grupo->tareas->pluck('user_id')->unique())
                ->orderBy('name')->get();
        }

        return view('livewire.admin.grupo-tarea-livewire', [
            'tareas_grupo' => $tareas_grupo,
            'tareasPaginadas' => $tareasPaginadas,
        ]);
    }

    public function mount()
    {
        $this->isAdmin = Auth::user() && Auth::user()->role === 'admin';
        $this->allEmpleados = User::select('id', 'name')->where('status', 1)->orderBy('name')->get();
        $this->allClientes  = Cliente::select('id', 'name')->where('status', 1)->orderBy('name')->get();
        $this->selectEstatusTarea = EstatusTareaEnum::cases();


        $this->losDias = DiasEnum::cases();
        $this->perPage = PaginacionEnum::Diez->value; // Default pagination value
        $this->paginacion = PaginacionEnum::cases();
    }

    public function create()
    {
        $this->reset(['grupo_tarea_id', 'tarea', 'estatus', 'fecha', 'horas', 'cliente_id', 'user_id', 'tarea_id', 'editMode', 'deleteMode', 'descripcion', 'fecha_inicio', 'fecha_fin', 'dias', 'observacion']);

        $this->editMode   = false;
        $this->deleteMode = false;
        $this->createMode = true;
        $this->showTarea = null;
        $this->dashboard = false;
    }

    public function store()
    {
        try {
            DB::beginTransaction();
            $this->validate($this->reglasGrupoTarea());
            $userIds = is_array($this->user_id) ? $this->user_id : [$this->user_id];
            $diasSel = is_array($this->dias) ? $this->dias : [$this->dias];

            $grupo_tarea = GrupoTarea::create([
                'descripcion' => $this->descripcion,
                'fecha_inicio' => $this->fecha_inicio,
                'fecha_fin' => $this->fecha_fin,
                'dias' => $diasSel
            ]);

            // Obtener todas las fechas de los días seleccionados
            $fechas_tareas = $this->obtenerFechasPorDias($this->fecha_inicio, $this->fecha_fin, $diasSel);

            foreach ($userIds as $uid) {
                foreach ($fechas_tareas as $fecha) {
                    Tarea::create([
                        'tarea'       => $this->tarea,
                        'fecha'       => $fecha,
                        'horas'       => $this->horas,
                        'observacion' => $this->observacion,
                        'user_id'     => $uid,
                        'cliente_id'  => $this->cliente_id,
                        'grupo_tarea_id' => $grupo_tarea->id
                    ]);
                }
            }
            DB::commit();
            $this->dashboard  = true;
            $this->createMode = false;

            session()->flash('success', __('Tasks created successfully'));
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error('Error en store: ' . $th->getMessage());
            session()->flash('error', __('Error creating tasks'));
            throw $th;
        }
    }

    public function show($id)
    {
        $this->showTarea = $grupo = GrupoTarea::findOrFail($id);
        $this->descripcion = $grupo->descripcion;
        $this->grupo_tarea_id = $grupo->id;
        $this->fecha_inicio = $grupo->fecha_inicio;
        $this->fecha_fin = $grupo->fecha_fin;
        $this->dias = $grupo->dias;
        $this->editMode = false;
        $this->deleteMode = false;
        $this->dashboard = false;
        $this->createMode = false;
    }

    public function update()
    { // Actualizar tarea existente
        try {
            $this->validate($this->reglasTarea($this->tarea_id));
            $tarea = Tarea::findOrFail($this->tarea_id);
            $id = $tarea->grupo_tarea_id; // Mantener el grupo de tarea
            $tarea->update([
                'tarea'      => $this->tarea,
                'estatus'    => $this->estatus,
                'fecha'      => $this->fecha,
                'horas'      => $this->horas,
                'cliente_id' => $this->cliente_id,
                'user_id'    => $this->user_id ?: null,
                'observacion' => $this->observacion,
            ]);

            $this->editTarea = false;
            $this->dashboard = false;
            $this->show($id);

            session()->flash('success', __('Task updated successfully'));
        } catch (\Throwable $th) {
            Log::error('Error en update: ' . $th->getMessage());
            throw $th;
        }
    }

    private function reglasTarea($tarea_id = null)
    {
        return (new TareaRequest())->rules($tarea_id);
    }

    private function reglasGrupoTarea()
    {
        return (new TareaGrupoRequest())->rules();
    }

    public function obtenerFechasPorDias($fecha_inicio, $fecha_fin, $diasSeleccionados)
    {
        $fechas = [];
        $start = Carbon::parse($fecha_inicio);
        $end = Carbon::parse($fecha_fin);

        while ($start->lte($end)) {
            if (in_array($start->format('l'), $diasSeleccionados)) {
                $fechas[] = $start->toDateString();
            }
            $start->addDay();
        }
        return $fechas;
    }

    public function closeModal_grupo()
    {
        $this->editMode   = false;
        $this->deleteMode = false;
        $this->deleteGroupMode = false;
        $this->createMode = false;
        $this->showTarea = null;
        $this->dashboard = true;
        $this->filtroEstatus = '';
    }

    public function closeModalGrupo()
    {
        $this->reset(['tarea', 'estatus', 'fecha', 'horas', 'cliente_id', 'user_id', 'observacion',  'tarea_id']);

        $this->editTarea = false;
        $this->dashboard = false;
        $this->deleteMode = false;
        $this->deleteGroupMode = false;
        $this->show($this->grupo_tarea_id);
    }

    public function confirmDelete($id)
    {
        $tarea = Tarea::findOrFail($id);
        $this->tarea_id = $tarea->id;
        $this->tarea = $tarea->tarea;
        $this->estatus = $tarea->estatus;
        $this->fecha = $tarea->fecha;
        $this->horas = $tarea->horas;
        $this->cliente_id = $tarea->cliente_id;
        $this->user_id = $tarea->user_id;
        $this->observacion = $tarea->observacion;
        $this->deleteMode = true;

        $this->editMode   = false;
        $this->createMode = false;
        $this->showTarea = null;
        $this->dashboard = false;

        // Log::info([$this->tarea_id, $this->tarea,  $this->estatus,  $this->fecha,  $this->horas, $this->cliente_id,  $this->user_id,  $this->observacion]);
    }

    public function confirmDeleteGrupo($id)
    {
        $this->showTarea = $grupo = GrupoTarea::findOrFail($id);
        $this->grupo_tarea_id = $grupo->id;
        $this->descripcion = $grupo->descripcion;
        $this->fecha_inicio = $grupo->fecha_inicio;
        $this->fecha_fin = $grupo->fecha_fin;
        $this->dias = $grupo->dias;

        $this->deleteGroupMode = true;

        $this->deleteMode = false;
        $this->editMode   = false;
        $this->createMode = false;
        $this->dashboard = false;
    }

    public function deleteGrupo()
    {
        try {
            DB::beginTransaction();
            $grupo = GrupoTarea::findOrFail($this->grupo_tarea_id);
            $grupo->tareas()->delete(); // Eliminar todas las tareas asociadas
            $grupo->delete();

            DB::commit();
            session()->flash('success', __('Group task deleted successfully'));
            $this->closeModal_grupo();
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error('Error en deleteGrupo: ' . $th->getMessage());
            session()->flash('error', __('Error deleting group task'));
        }
    }

    public function updatedPaginacion($value)
    {
        $this->perPage = $value;
        $this->resetPage();
    }
}
