<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Enums\PaginacionEnum;
use App\Enums\DiasEnum;
use App\Http\Requests\TareaRequest;
use App\Models\Cliente;
use App\Models\GrupoTarea;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GrupoTareaLivewire extends Component
{

    use WithPagination;

    public $search = '';
    public $sortField = 'id';
    public $sortDirection = 'desc';
    public $isAdmin = false;
    public $perPage;
    public $paginacion;

    public $allEmpleados = [];
    public $allClientes = [];
    public $losDias;
    public $dias = [];

    public $editMode   = false;
    public $createMode = false;
    public $deleteMode = false;
    public $dashboard  = true;

    public $showTarea  = null;

    public $descripcion, $fecha_inicio, $fecha_fin;
    public $tareas,  $tarea, $tarea_id;
    public $estatus, $fecha, $horas = 1, $user_id, $cliente_id, $observacion, $grupo_tarea_id;

    protected $updatesQueryString = [
        ['search'  => ['except' => '']],
        ['perPage' => ['except' => PaginacionEnum::Diez->value]]
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
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $tareasPaginadas = null;
        if ($this->grupo_tarea_id && !$this->dashboard) {
            $grupo = GrupoTarea::find($this->grupo_tarea_id);
            $tareasPaginadas = $grupo
                ? $grupo->tareas()->orderBy('fecha', 'asc')->paginate(10)
                : null;
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

        $this->losDias = DiasEnum::cases();
        $this->perPage = PaginacionEnum::Diez->value; // Default pagination value
        $this->paginacion = PaginacionEnum::cases();
    }

    public function create()
    {
        $this->reset(['grupo_tarea_id', 'tarea', 'estatus', 'fecha', 'horas', 'cliente_id', 'user_id', 'tarea_id', 'editMode', 'deleteMode', 'descripcion', 'fecha_inicio', 'fecha_fin']);

        $this->editMode   = false;
        $this->deleteMode = false;
        $this->createMode = true;
        $this->showTarea = null;
        $this->dashboard = false;
    }

    public function store()
    {
        // $this->validate($this->reglasTarea());
        $userIds = is_array($this->user_id) ? $this->user_id : [$this->user_id];
        $diasSel = is_array($this->dias) ? $this->dias : [$this->dias];

        $grupo_tarea = GrupoTarea::create([
            'descripcion' => $this->descripcion
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
        $this->dashboard  = true;
        $this->createMode = false;

        session()->flash('success', __('Tasks created successfully'));
    }

    public function show($id)
    {

        $this->showTarea = $grupo = GrupoTarea::findOrFail($id);
        $this->descripcion = $grupo->descripcion;
        $this->grupo_tarea_id = $grupo->id;

        // Paginar tareas relacionadas
        // $this->showTarea = $grupo->tareas()->orderBy('fecha', 'desc')->paginate(10);


        // dd($grupo_tarea->tareas[0]['fecha']);

        $this->editMode = false;
        $this->deleteMode = false;
        $this->dashboard = false;
        $this->createMode = false;
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    private function reglasTarea($tarea_id = null)
    {
        return (new TareaRequest())->rules($tarea_id);
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

    public function closeModal()
    {
        $this->editMode   = false;
        $this->deleteMode = false;
        $this->createMode = false;
        $this->showTarea = null;
        $this->dashboard = true;
    }
}
