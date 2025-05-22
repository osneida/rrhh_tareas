<?php

use App\Livewire\ClienteLivewire;
use App\Livewire\EmpleadoLivewire;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\TareaLivewire;
use Illuminate\Support\Facades\Route;
//use Illuminate\Support\Facades\Session;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    // Route::get('language/lang', [LanguageController::class, 'changeLanguage'])->name('lang');
});

//cree un middleware role
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/tareas', TareaLivewire::class)->name('tareas.index');
    Route::get('/clientes', ClienteLivewire::class)->name('clientes.index');
    Route::get('/empleados', EmpleadoLivewire::class)->name('empleados.index');

});


Route::get('lang/{lang}', function ($lang) {
    if (array_key_exists($lang, config('languages'))) {
        // Session::put('locale', $lang);
        session(['locale' => $lang]);
    }
    return redirect()->back();
})->name('lang');



require __DIR__ . '/auth.php';
