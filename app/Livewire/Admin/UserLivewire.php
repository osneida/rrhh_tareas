<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class UserLivewire extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $isAdmin = false;
    public $perPage = 10;

    protected $updatesQueryString = [
        ['search' => ['except' => '']],
        ['status' => ['except' => '']],
        ['sortField' => ['except' => 'name']],
        ['sortDirection' => ['except' => 'asc']],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleStatus(User $user)
    {
        $user->status = !$user->status;
        $user->save();
    }

    public function mount()
    {
        $this->isAdmin = Auth::user() && Auth::user()->role === 'admin';
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
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.user-livewire', [
            'users' => $users,
        ]);
    }
}
