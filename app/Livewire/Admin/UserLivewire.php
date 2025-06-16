<?php

namespace App\Livewire\Admin;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use App\Enums\RoleEnum;
use App\Enums\StatusEnum;
use App\Enums\PaginacionEnum;
use App\Livewire\Trait\FuncionesTrait;

class UserLivewire extends Component
{
    use WithPagination, FuncionesTrait;

    public $editMode = false;
    public $createMode = false;
    public $status = '';

    public $selectStatus;
    public $roles;

    public $deleteMode = false;
    public $showModal = false;

    public $user_id, $name, $email, $password, $password_confirmation, $role;

    protected $updatesQueryString = [
        ['search' => ['except' => '']],
        ['status' => ['except' => '']],
        ['perPage' => ['except' => PaginacionEnum::Diez->value]],
        ['ordenCampo' => ['except' => 'name']],
        ['ordenDireccion' => ['except' => 'asc']],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function toggleStatus(User $user)
    {
        $user->status = !$user->status;
        $user->save();

        session()->flash('success', (__('Status updated successfully')));
    }

    public function mount()
    {
        $this->ordenCampo = 'name';
        $this->ordenDireccion = 'asc';

        $this->isAdmin = Auth::user() && Auth::user()->role === 'admin';
        $this->roles = RoleEnum::cases();
        $this->selectStatus = StatusEnum::cases();
        $this->paginacion = PaginacionEnum::cases();
        $this->perPage = PaginacionEnum::Diez->value; // Default pagination value

    }

    public function render()
    {
        $users = User::query()
            ->when(
                $this->search,
                fn($q) =>
                $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                })
            )
            ->when($this->status !== '', function ($q) {
                $q->where('status', (int)$this->status);
            })
            ->orderBy($this->ordenCampo, $this->ordenDireccion)
            ->paginate($this->perPage);


        if ($this->editMode || $this->createMode) {
            return view('livewire.admin.user-edit-livewire', [
                'users' => $users,
            ]);
        } else {
            return view('livewire.admin.user-livewire', [
                'users' => $users,
            ]);
        }
    }

    public function create()
    {
        $this->reset(['name', 'email', 'password', 'password_confirmation', 'role', 'user_id']);
        $this->role = RoleEnum::empleado->value;
        $this->editMode   = false;
        $this->deleteMode = false;
        $this->showModal  = false;
        $this->createMode  = true;
    }

    public function store()
    {
        try {
            $this->validate($this->reglasUser());

            User::create([
                'name'      => $this->name,
                'email'     => $this->email,
                'role'      => $this->role,
                'password'  => $this->password,
                'status'    => true, // Default status
            ]);

            $this->showModal = false;
            $this->createMode  = false;

            session()->flash('success', __('User created successfully'));
        } catch (\Throwable $th) {
            Log::error('Error en store: ' . $th->getMessage());
            throw $th;
        }
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->user_id = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;

        $this->editMode = true;
        $this->deleteMode = false;
    }

    public function update()
    {
        try {

            $user = User::findOrFail($this->user_id);
            $this->validate($this->reglasUser($this->user_id));

            $user->update([
                'name'  => $this->name,
                'email' => $this->email,
                'role'  => $this->role,
            ]);

            $this->editMode = false;

            session()->flash('success',  __('User updated successfully'));
        } catch (\Throwable $th) {
            Log::error('Error en update: ' . $th->getMessage());
            throw $th;
        }
    }

    public function show($id)
    {
        $user =  User::findOrFail($id);
        $this->name  = $user->name;
        $this->email = $user->email;
        $this->role  = $user->role;
        $this->user_id = $user->id;

        $this->showModal  = true;
        $this->editMode   = false;
        $this->deleteMode = false;
    }

    public function confirmDelete($id)
    {
        $this->show($id);
        $this->deleteMode = true; // Activar modo eliminar
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            // No permitir eliminar al usuario logueado
            if (Auth::user()->id == $user->id) {
                $this->closeModal();
                session()->flash('error', __('You cannot delete your own user'));
                return;
            }

            if ($user->tareas()->count() > 0) {
                $this->closeModal();
                session()->flash('error', __('The user cannot be deleted because he has tasks assigned to him, you can change the status'));
                return;
            }

            $user->delete();

            $this->closeModal();

            session()->flash('success', __('Employee deleted successfully'));
        } catch (\Throwable $th) {
            Log::error('Error al eliminar destroy empleado: ' . $th->getMessage());
            throw $th;
        }
    }


    private function reglasUser($userID = null)
    {
        return (new UserRequest())->rules($userID);
    }

    public function closeModal()
    {
        $this->deleteMode = false;
        $this->showModal = false; // Cierra el modal
        $this->editMode = false;
        $this->createMode  = false;
    }
}
