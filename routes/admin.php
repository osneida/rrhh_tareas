<?php

use Illuminate\Support\Facades\Route;

use App\Livewire\Admin\TareaLivewire;
use App\Livewire\Admin\ClienteLivewire;
use App\Livewire\Admin\GrupoTareaLivewire;
use App\Livewire\Admin\UserLivewire;

//cree un middleware role
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/tareas', TareaLivewire::class)->name('tareas.index');
    Route::get('/tarea-grupo', GrupoTareaLivewire::class)->name('tarea-grupo.index');
    Route::get('/clientes', ClienteLivewire::class)->name('clientes.index');
    Route::get('/empleados', UserLivewire::class)->name('empleados.index');
});
