<?php

namespace App\Livewire\Trait;

trait FuncionesTrait
{
    public $search = '';
    public $perPage;
    public $paginacion;
    public $ordenCampo = 'id';
    public $ordenDireccion = 'desc';

    public $isAdmin = false;

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

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}
