<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tarea;
use App\Models\User;
use App\Models\Cliente;

class TareaLivewire extends Component
{
    use WithPagination;

    public $tareas, $tarea, $tarea_id;
    public $tarea_nombre, $estatus, $fecha, $horas, $user_id, $cliente_id, $observacion;
    public $modo = 'index';

    // NUEVAS PROPIEDADES PARA FILTROS Y ORDEN
    public $search = '';
    public $filtroEstatus = '';
    public $filtroEmpleado = '';
    public $filtroCliente = '';
    public $ordenCampo = 'fecha';
    public $ordenDireccion = 'desc';
    public $showTarea;
    public $showModal = false;
    public $editMode = false;
    public $allEmpleados = [];
    public $allClientes = [];
    public $isAdmin = false;

    public function mount()
    {
        $this->allEmpleados = User::all();
        $this->allClientes = Cliente::all();
        $this->isAdmin = true;
    }

    public function render()
    {
        $isAdmin = true; //Auth::user() && Auth::user()->hasRole('Admin')
        $query = Tarea::with(['user', 'cliente']);

        // FILTRO DE BÚSQUEDA
        if ($this->search) {
            $query->where('tarea', 'like', '%' . $this->search . '%');
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

      //  $isAdmin = $this->isAdmin;
        // ORDENAMIENTO
        $query->orderBy($this->ordenCampo, $this->ordenDireccion);
        $tareas_pag = $query->paginate(10);
        $empleados = User::all();
        $clientes = Cliente::all();
        return view('livewire.tarea-livewire', compact('tareas_pag','empleados', 'clientes', 'isAdmin'));
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
        $this->reset(['tarea', 'estatus', 'fecha', 'horas', 'cliente_id','user_id', 'tarea_id', 'editMode']);
        $this->showModal = true;
        $this->editMode = false;
    }

    public function store()
    {
       // $this->validate();

        $tarea = Tarea::create([
            'tarea' => $this->tarea,
            'estatus' => $this->estatus ?? 'Pendiente',
            'fecha' => $this->fecha,
            'horas' => $this->horas,
            'cliente_id' => $this->cliente_id,
            'user_id' => $this->user_id
        ]);

        $this->showModal = false;
        session()->flash('success', 'Tarea creada correctamente.');
    }

    public function show($id)
    {
        $this->showTarea = Tarea::with(['cliente', 'user'])->findOrFail($id);
        $this->showModal = true;
        $this->editMode = false;
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
    }

    public function update()
    {
        //$this->validate();

        $tarea = Tarea::findOrFail($this->tarea_id);
        $tarea->update([
            'tarea' => $this->tarea,
            'estatus' => $this->estatus,
            'fecha' => $this->fecha,
            'horas' => $this->horas,
            'cliente_id' => $this->cliente_id,
            'user_id' => $this->user_id,

        ]);


        $this->showModal = false;
        session()->flash('success', 'Tarea actualizada correctamente.');
    }

    public function destroy($id)
    {
        $tarea = Tarea::findOrFail($id);
        $tarea->delete();
        session()->flash('success', 'Tarea eliminada correctamente.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->showTarea = null;
    }
}
