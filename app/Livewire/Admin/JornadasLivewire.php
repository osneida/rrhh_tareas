<?php

namespace App\Livewire\Admin;

use App\Enums\EstatusTareaEnum;
use App\Enums\PaginacionEnum;
use App\Models\Cliente;
use App\Models\JornadaLaboral;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;


class JornadasLivewire extends Component
{
    use WithPagination;

    public $jornadas, $jornada_id;
    public $fecha, $hora_inicio, $hora_fin, $tarea_id;
    public $search = '';
    public $filtroEmpleado = '';
    public $isAdmin = false;
    public $allEmpleados = [];
    public $allClientes = [];
    public $selectStatus;
    public $paginacion;
    public $selectEstatusTarea;
    public $perPage;
    public $ordenCampo = 'fecha';
    public $ordenDireccion = 'desc';

    public function render()
    {
        $this->jornadas = JornadaLaboral::with('tarea')->select(
            'id',
            'fecha',
            'hora_inicio',
            'hora_fin',
            'tarea_id',
            DB::raw('HOUR(TIMEDIFF(hora_fin, hora_inicio)) as horas_transcurridas'),
            DB::raw('MINUTE(TIMEDIFF(hora_fin, hora_inicio)) as minutos_transcurridos')
        )->orderByDesc('fecha')
            ->get();

                  // $query->orderBy($this->ordenCampo, $this->ordenDireccion);
       // $this->jornadas = $query->paginate($this->perPage);

        return view('livewire.admin.jornadas-livewire');
    }

    public function mount()
    {
        $this->allEmpleados = User::select('id', 'name')->orderBy('name')->get();
        $this->allClientes  = Cliente::select('id', 'name')->orderBy('name')->get();
        $this->isAdmin = Auth::user() && Auth::user()->role === 'admin';

        $this->paginacion = PaginacionEnum::cases();
        $this->perPage = PaginacionEnum::Diez->value; // Default pagination value
        $this->selectEstatusTarea = EstatusTareaEnum::cases();
    }

    public function ordenarPor($campo)
    {
        if ($this->ordenCampo === $campo) {
            $this->ordenDireccion = $this->ordenDireccion === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ordenCampo = $campo;
            $this->ordenDireccion = 'asc';
        }
    }
}
