<?php

namespace App\Livewire\Empleado;

use App\Enums\EstatusTareaEnum;
use App\Enums\PaginacionEnum;
use App\Livewire\Trait\TareaTrait;
use App\Models\Cliente;
use App\Models\Tarea;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

use Illuminate\Support\Facades\Log;

class MisTareasLivewire extends Component
{
    use WithPagination, TareaTrait;

    public $isAdmin = false;
    public $allClientes = [];
    public $perPage;

    public $selectStatus;
    public $paginacion;
    public $selectEstatusTarea;
    public $search = '';
    public $filtroEstatus = '';
    public $filtroCliente = '';
    public $tareas;

    public function mount()
    {
        $this->paginacion = PaginacionEnum::cases();
        $this->perPage = PaginacionEnum::Diez->value; // Default pagination value
        $this->selectEstatusTarea = EstatusTareaEnum::cases();
    }

    public function render()
    {
        try {
            $query = $this->buildQuery();

            $tareas_pag = $query->paginate($this->perPage);
            return view('livewire.empleado.mis-tareas-livewire', compact('tareas_pag'));
        } catch (\Throwable $th) {
            Log::error('Error en update: ' . $th->getMessage());
            throw $th;
        }
    }

    private function buildQuery()
    {
        $clientes = $query  = Tarea::with(['cliente:id,name', 'user:id,name'])->where('user_id', Auth::user()->id);
        $this->allClientes  = Cliente::select('id', 'name')->whereIn('id', $clientes->pluck('cliente_id')->unique())->orderBy('name')->get();

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

        if ($this->filtroCliente) {
            $query->where('cliente_id', $this->filtroCliente);
        }

        // ORDENAMIENTO
        if ($this->ordenCampo === 'user_name') {
            $query->join('users', 'tareas.user_id', '=', 'users.id')
                ->orderBy('users.name', $this->ordenDireccion)
                ->select('tareas.*');
        } elseif ($this->ordenCampo === 'cliente_name') {
            $query->join('clientes', 'tareas.cliente_id', '=', 'clientes.id')
                ->orderBy('clientes.name', $this->ordenDireccion)
                ->select('tareas.*');
        } else {
            $query->orderBy($this->ordenCampo, $this->ordenDireccion);
        }

        return $query;
    }
}
