<?php

use App\Livewire\Empleado\MisJornadasLivewire;
use App\Livewire\Empleado\MisTareasLivewire;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/mis-tareas', MisTareasLivewire::class)->name('mis-tareas');
    Route::get('/mis-jornadas', MisJornadasLivewire::class)->name('mis-jornadas');

});
