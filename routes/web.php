<?php

use App\Livewire\DashboardLivewire;
use Illuminate\Support\Facades\Route;

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;

Route::get('/', function () {
    return view('welcome');
})->name('home');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardLivewire::class)->name('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

Route::get('lang/{lang}', function ($lang) {
    if (array_key_exists($lang, config('languages'))) {
        session(['locale' => $lang]);
    }
    return redirect()->back();
})->name('lang');


require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/empleado.php';

