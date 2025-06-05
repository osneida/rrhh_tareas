<?php

namespace App\Livewire\Trait;

use App\Models\Tarea;
use Illuminate\Support\Facades\Log;

trait TareaTrait
{
    public function edit($id)
    {
        $tarea = Tarea::findOrFail($id);
        $this->tarea_id = $tarea->id;
        $this->tarea = $tarea->tarea;
        $this->estatus = $tarea->estatus;
        $this->fecha = $tarea->fecha;
        $this->horas = $tarea->horas;
        $this->cliente_id = $tarea->cliente_id;
        $this->user_id = $tarea->user_id;
        if (method_exists($this, 'closeModal')) {

            $this->showModal = true;
            $this->editMode = true;
            $this->deleteMode = false;
        }

        if (method_exists($this, 'closeModal_grupo')) {
            $this->closeModal_grupo();
            $this->editTarea = true;
            $this->dashboard = false;
        }
    }

    public function destroy($id)
    {
        try {
            $tarea = Tarea::findOrFail($id);
            $tarea->delete();

            if (method_exists($this, 'closeModal')) {
                $this->closeModal();
            }

            if (method_exists($this, 'closeModalGrupo')) {
                $this->closeModalGrupo();
            }

            $this->deleteMode = false;

            session()->flash('success', __('Task deleted successfully'));
        } catch (\Throwable $th) {
            Log::error('Error al eliminar destroy: ' . $th->getMessage());
            throw $th;
        }
    }
}
