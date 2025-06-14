<?php

namespace App\Livewire;

use App\Enums\EstatusTareaEnum;
use App\Models\JornadaLaboral;
use App\Models\Tarea;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardLivewire extends Component
{
    public $isAdmin = false;
    public $horas_inicio = []; // Array para almacenar las horas de inicio de cada tarea
    public $horas_fin    = [];   // Array para almacenar las horas de fin de cada tarea
    public $enables_inicio = [];
    public $enables_fin = [];
    public $horasIniciadas  = 0;
    public $horasPendientes = 0;
    public $horasCompletadas = 0;
    public $totalTareas     = 0;
    public $totalClientes   = 0;
    public $totalEmpleados  = 0;
    public $hoy, $user, $tarea_hoy, $hora_inicio, $hora_fin;

    public function render()
    {
        $this->tarea_hoy = Tarea::with('cliente')
            ->with('jornada_sintarea:hora_inicio,hora_fin,tarea_id')
            ->where('user_id', $this->user)
            ->where('fecha', $this->hoy)
            ->get();

        foreach ($this->tarea_hoy as $tarea) {
            if (is_null($tarea->jornada_sintarea->tarea_id)) {
                $this->enables_inicio[$tarea['id']] = 'enabled';
                $this->enables_fin[$tarea['id']] = 'disabled';
            } else {
                $this->horas_inicio[$tarea['id']] = $tarea->jornada_sintarea->hora_inicio;
                $this->horas_fin[$tarea['id']] = $tarea->jornada_sintarea->hora_fin;

                if ($tarea->jornada_sintarea->hora_fin == null) {
                    $this->enables_inicio[$tarea['id']] = 'disabled';
                    $this->enables_fin[$tarea['id']] = 'enabled';
                } else {
                    $this->enables_inicio[$tarea['id']] = 'disabled';
                    $this->enables_fin[$tarea['id']] = 'disabled';
                }
            }
        }

        return view('dashboard');
    }

    public function mount()
    {
        $this->isAdmin = Auth::user() && Auth::user()->role === 'admin';
        $this->user = Auth::user()->id;
        $this->totalTareas     = Tarea::total_tareas();
        $this->horasIniciadas  = Tarea::horasIniciada();
        $this->horasPendientes = Tarea::horasPendientes();
        $this->horasCompletadas = Tarea::horasCompletada();
        $this->totalClientes   = \App\Models\Cliente::total_clientes();
        $this->totalEmpleados  = \App\Models\User::total_empleados();

        date_default_timezone_set("Europe/Madrid");
        $this->hoy = date("Y-m-d");
    }

    public function guardar_hora_inicio($tarea)
    {
        try {
            DB::beginTransaction();

            date_default_timezone_set("Europe/Madrid");
            $this->hora_inicio = date("H:i:s");

            JornadaLaboral::updateOrCreate(
                ['tarea_id' => $tarea],
                [
                    'fecha'        => date("y/m/d"),
                    'hora_inicio'  => $this->hora_inicio,
                ]
            );

            $this->enables_inicio[$tarea] = 'disabled';
            $this->enables_fin[$tarea] = 'enabled';

            $tarea_update = Tarea::findOrFail($tarea);
            $tarea_update->update(['estatus' => EstatusTareaEnum::Iniciada->value]);

            $this->horasIniciadas += $tarea_update->horas;
            $this->horasPendientes -= $tarea_update->horas;
             DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar_hora_inicio: ' . $e->getMessage());
            return;
        }
    }

    public function guardar_hora_fin($tarea)
    {
        try {
            DB::beginTransaction();

            date_default_timezone_set("Europe/Madrid");
            $this->hora_fin = date("H:i:s");

            $jl = JornadaLaboral::where('tarea_id', $tarea);
            $jl->update(['hora_fin' => $this->hora_fin]);

            $this->enables_inicio[$tarea] = 'disabled';
            $this->enables_fin[$tarea] = 'disabled';

            $tarea_update = Tarea::find($tarea);
            $tarea_update->update(['estatus' => EstatusTareaEnum::Finalizada->value]);

            $this->horasIniciadas -= $tarea_update->horas;
            $this->horasCompletadas += $tarea_update->horas;

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar_hora_fin: ' . $e->getMessage());
            session()->flash('error', __('An error occurred while finishing the task.'));
        }
    }
}
