<?php

namespace App\Livewire;

use App\Http\Requests\TareaRequest;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TareasExport;
use App\Models\Tarea;
use App\Models\User;
use App\Models\Cliente;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Stmt\TryCatch;

class TareaLivewire extends Component
{
    use WithPagination;

    public $tareas, $tarea, $tarea_id;
    public $tarea_nombre, $estatus, $fecha, $horas, $user_id, $cliente_id, $observacion;

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
    public $perPage = 10;

    public function mount()
    {
        $this->allEmpleados = User::select('id', 'name')->orderBy('name')->get();
        $this->allClientes  = Cliente::select('id', 'name')->where('estatus', 1)->orderBy('name')->get();
        $this->isAdmin = true; //Auth::user() && Auth::user()->hasRole('Admin')
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
            $isAdmin = $this->isAdmin;

            $query = $this->buildQuery();

            $tareas_pag = $query->paginate($this->perPage);
            $empleados  = $this->allEmpleados;
            $clientes   = $this->allClientes;
            return view('livewire.tarea-livewire', compact('tareas_pag', 'empleados', 'clientes', 'isAdmin'));
        } catch (\Throwable $th) {
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
        $this->reset(['tarea', 'estatus', 'fecha', 'horas', 'cliente_id', 'user_id', 'tarea_id', 'editMode', 'deleteMode']);
        $this->showModal = true;
        $this->editMode = false;
        $this->deleteMode = false;
    }

    public function store()
    {
        $this->validate($this->reglasTarea());

        if ($this->user_id) {
            foreach ($this->user_id as $user) {
                Tarea::create([
                    'tarea'      => $this->tarea,
                    'estatus'    => $this->estatus ?? 'Pendiente',
                    'fecha'      => $this->fecha,
                    'horas'      => $this->horas,
                    'cliente_id' => $this->cliente_id,
                    'user_id'    => $user
                ]);
            }
        } else {
            Tarea::create([
                'tarea'      => $this->tarea,
                'estatus'    => $this->estatus ?? 'Pendiente',
                'fecha'      => $this->fecha,
                'horas'      => $this->horas,
                'cliente_id' => $this->cliente_id,
            ]);
        }


        $this->showModal = false;
        session()->flash('success', 'Tarea creada correctamente.');
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
            $this->validate($this->reglasTarea());

            $tarea = Tarea::findOrFail($this->tarea_id);


            if ($this->user_id) {
                $tarea->update([
                    'tarea' => $this->tarea,
                    'estatus' => $this->estatus,
                    'fecha' => $this->fecha,
                    'horas' => $this->horas,
                    'cliente_id' => $this->cliente_id,
                    'user_id' => $this->user_id,
                ]);
            } else {
                $tarea->update([
                    'tarea' => $this->tarea,
                    'estatus' => $this->estatus,
                    'fecha' => $this->fecha,
                    'horas' => $this->horas,
                    'cliente_id' => $this->cliente_id,
                    'user_id' => null,
                ]);
            }



            $this->showModal = false;

            session()->flash('success', 'Tarea actualizada correctamente.');
        } catch (\Throwable $th) {
            throw $th;
            Log::info('Error en update ', $th);
        }
    }

    public function confirmDelete($id)
    {
        $this->show($id);
        $this->deleteMode = true; // Activar modo eliminar
    }

    public function destroy($id)
    {
        try {
            $tarea = Tarea::findOrFail($id);
            $tarea->delete();

            $this->deleteMode = false;
            $this->showModal = false; // Cierra el modal
            $this->showTarea = null;
            session()->flash('success', 'Tarea eliminada correctamente.');
        } catch (\Throwable $th) {
            throw $th;
            Log::info('Error al eliminar destroy ', $th);
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->showTarea = null;
    }

    private function reglasTarea()
    {
        return (new TareaRequest())->rules();
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
