<?php

namespace App\Livewire\Trait;

use App\Models\Tarea;
use Illuminate\Support\Facades\Log;

trait TareaDeleteTrait
{
    public $deleteMode = false;


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

