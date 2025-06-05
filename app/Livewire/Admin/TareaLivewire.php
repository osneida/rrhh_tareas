<?php

namespace App\Livewire\Admin;

use App\Http\Requests\TareaRequest;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TareasExport;
use App\Models\Tarea;
use App\Models\User;
use App\Models\Cliente;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Enums\EstatusTareaEnum;
use App\Enums\PaginacionEnum;
use App\Livewire\Trait\TareaDeleteTrait;
class TareaLivewire extends Component
{
    use WithPagination, TareaDeleteTrait;

    public $tareas, $tarea, $tarea_id;
    public $estatus, $fecha, $horas = 1, $user_id, $cliente_id, $observacion;

    //  PROPIEDADES PARA FILTROS Y ORDEN
    public $search = '';
    public $filtroEstatus = '';
    public $filtroEmpleado = '';
    public $filtroCliente = '';
    public $ordenCampo = 'id';
    public $ordenDireccion = 'desc';
    public $showTarea;
    public $showModal = false;
    public $editMode = false;
    public $deleteMode = false;
    public $allEmpleados = [];
    public $allClientes = [];
    public $isAdmin = false;
    public $perPage;

    public $selectStatus;
    public $paginacion;
    public $selectEstatusTarea;

    public function mount()
    {
        $this->allEmpleados = User::select('id', 'name')->where('status', 1)->orderBy('name')->get();
        $this->allClientes  = Cliente::select('id', 'name')->where('status', 1)->orderBy('name')->get();
        $this->isAdmin = Auth::user() && Auth::user()->role === 'admin';

        $this->paginacion = PaginacionEnum::cases();
        $this->perPage = PaginacionEnum::Diez->value; // Default pagination value
        $this->selectEstatusTarea = EstatusTareaEnum::cases();
    }

    private function buildQuery()
    {
        $query = Tarea::with(['cliente:id,name', 'user:id,name']);

        // FILTRO DE BÚSQUEDA
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('tarea', 'like', '%' . $this->search . '%')
                    ->orWhere('fecha', 'like', '%' . $this->search . '%');
            });
        }

        // FILTROS SELECT
        if ($this->filtroEstatus) {
            $query->where('estatus', $this->filtroEstatus);
        }
        if ($this->filtroEmpleado) {
            $query->where('user_id', $this->filtroEmpleado);
        }
        if ($this->filtroCliente) {
            $query->where('cliente_id', $this->filtroCliente);
        }

        // ORDENAMIENTO
        $query->orderBy($this->ordenCampo, $this->ordenDireccion);

        return $query;
    }

    public function render()
    {
        try {
            $query = $this->buildQuery();

            $tareas_pag = $query->paginate($this->perPage);
            return view('livewire.admin.tarea-livewire', compact('tareas_pag'));
        } catch (\Throwable $th) {
            Log::error('Error en update: ' . $th->getMessage());
            throw $th;
        }
    }

    // MÉTODO PARA ORDENAR POR COLUMNA
    public function ordenarPor($campo)
    {
        if ($this->ordenCampo === $campo) {
            $this->ordenDireccion = $this->ordenDireccion === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ordenCampo = $campo;
            $this->ordenDireccion = 'asc';
        }
    }

    public function create()
    {
        $this->reset(['tarea', 'estatus', 'fecha', 'horas', 'cliente_id', 'user_id', 'observacion', 'tarea_id', 'editMode', 'deleteMode']);
        $this->showModal = true;
        $this->editMode = false;
        $this->deleteMode = false;
    }

    public function store()
    {
        $this->validate($this->reglasTarea());
        $userIds = is_array($this->user_id) ? $this->user_id : [$this->user_id];

        foreach ($userIds as $uid) {
            Tarea::create([
                'tarea'       => $this->tarea,
                'fecha'       => $this->fecha,
                'horas'       => $this->horas,
                'observacion' => $this->observacion,
                'user_id'     => $uid,
                'cliente_id'  => $this->cliente_id,
            ]);
        }

        $this->showModal = false;
        session()->flash('success', __('Task created successfully'));
    }

    public function show($id)
    {
        $this->showTarea = $tarea =  Tarea::with(['cliente:id,name', 'user:id,name'])->findOrFail($id);
        $this->tarea_id = $tarea->id;
        $this->tarea = $tarea->tarea;
        $this->estatus = $tarea->estatus;
        $this->fecha = $tarea->fecha;
        $this->horas = $tarea->horas;
        $this->cliente_id = $tarea->cliente_id;
        $this->user_id = $tarea->user_id;
        $this->observacion = $tarea->observacion;

        $this->showModal = true;
        $this->editMode = false;
        $this->deleteMode = false;
    }

    public function edit($id)
    {
        $tarea = Tarea::findOrFail($id);
        $this->tarea_id = $tarea->id;
        $this->tarea = $tarea->tarea;
        $this->estatus = $tarea->estatus;
        $this->fecha = $tarea->fecha;
        $this->horas = $tarea->horas;
        $this->cliente_id = $tarea->cliente_id;
        $this->user_id = $tarea->user_id; //->pluck('id')->toArray() ?? [];
        $this->showModal = true;
        $this->editMode = true;
        $this->deleteMode = false;
    }

    public function update()
    {
        try {
            $this->validate($this->reglasTarea($this->tarea_id));
            $tarea = Tarea::findOrFail($this->tarea_id);

            $tarea->update([
                'tarea'      => $this->tarea,
                'estatus'    => $this->estatus,
                'fecha'      => $this->fecha,
                'horas'      => $this->horas,
                'cliente_id' => $this->cliente_id,
                'user_id'    => $this->user_id ?: null, // Si está vacío, lo pone en null
            ]);

            $this->showModal = false;

            session()->flash('success', __('Task updated successfully'));
        } catch (\Throwable $th) {
            Log::error('Error en update: ' . $th->getMessage());
            throw $th;
        }
    }

    public function confirmDelete($id)
    {
        $this->show($id);
        $this->deleteMode = true; // Activar modo eliminar
    }


    public function closeModal()
    {
        $this->deleteMode = false;
        $this->showModal = false; // Cierra el modal
        $this->showTarea = null;
    }

    private function reglasTarea($tarea_id = null)
    {
        return (new TareaRequest())->rules($tarea_id);
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function exportExcel()
    {
        $fileName = 'tareas_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        $query = $this->buildQuery();
        $data = $query->get(); // Reutilizar la lógica de la consulta y obtener los datos

        return Excel::download(
            new TareasExport($data),
            $fileName
        );
    }
}
