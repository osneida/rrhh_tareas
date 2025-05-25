<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;


class UserLivewire extends Component
{

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public $isAdmin = false;
    public $filtroEstatus = '';
    public $cambioEstatus = 'no';
    public $perPage = 10;
    public $ordenCampo = 'id';
    public $ordenDireccion = 'desc';
    public $search = '';

    public $showUser;
    public $showModal = false;
    public $editMode = false;
    public $deleteMode = false;


    public function mount()
    {
        $this->isAdmin = Auth::user() && Auth::user()->role === 'admin';
    }

    public function render()
    {
        try {
            $query = User::query();

            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            }
            if ($this->filtroEstatus !== '' && $this->filtroEstatus !== null) {
                $query->where('status', $this->filtroEstatus);
            }

            $query->orderBy($this->ordenCampo, $this->ordenDireccion);

            $users = $query->paginate($this->perPage);

            return view('livewire.admin.user-livewire', compact('users'));
        } catch (\Throwable $th) {
            Log::error('Error en update: ' . $th->getMessage());
            throw $th;
        }
    }


    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        $this->showModal = false;
        session()->flash('success', __('Client created successfully'));
    }




    public function closeModal()
    {
        $this->deleteMode = false;
        $this->showModal = false; // Cierra el modal
        $this->showUser = null;
    }




    /*
        public function updatingSearch()
    {
        $this->resetPage();
    }

      public function updatingPerPage()
    {
        $this->resetPage();
    }

  public function updatingfiltroEstatus()
    {
        $this->resetPage();
    }*/
}
