<?php

namespace App\Livewire\Admin;

use App\Http\Requests\ClienteRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\WithPagination;
use Livewire\Component;
use App\Models\Cliente;
use App\Enums\StatusEnum;
use App\Enums\PaginacionEnum;
use App\Livewire\Trait\FuncionesTrait;

class ClienteLivewire extends Component
{
    use WithPagination, FuncionesTrait;

    public $name, $address, $cif, $mail, $phone, $status, $cliente_id;
    public $filtroEstatus = '';
    public $showCliente;
    public $head = [];
    public $showModal = false;
    public $editMode = false;
    public $deleteMode = false;
    public $selectStatus;

    public function mount()
    {
        $this->isAdmin = Auth::user() && Auth::user()->role === 'admin';
        $this->selectStatus = StatusEnum::cases();
        $this->paginacion = PaginacionEnum::cases();
        $this->perPage = PaginacionEnum::Diez->value; // Default pagination value
        $this->head = [
            'id' => __('#'),
            'name' => __('Name'),
            'address' => __('Address'),
            'cif' => __('CIF'),
            'mail' => __('Mail'),
            'phone' => __('Phone'),
            'status' => __('Status')
        ];
    }

    public function render()
    {
        try {
            $query = Cliente::query();

            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('address', 'like', '%' . $this->search . '%')
                        ->orWhere('cif', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%')
                        ->orWhere('mail', 'like', '%' . $this->search . '%');
                });
            }

            if ($this->filtroEstatus !== '' && $this->filtroEstatus !== null) {
                $query->where('status', $this->filtroEstatus);
            }

            $query->orderBy($this->ordenCampo, $this->ordenDireccion);

            $clientes = $query->paginate($this->perPage);

            return view('livewire.admin.cliente-livewire', compact('clientes'));
        } catch (\Throwable $th) {
            Log::error('Error en render: ' . $th->getMessage());
            throw $th;
        }
    }

    public function show($id)
    {
        $this->showCliente = $cliente =  Cliente::findOrFail($id);
        $this->name = $cliente->name;
        $this->address = $cliente->address;
        $this->cif = $cliente->cif;
        $this->mail = $cliente->mail;
        $this->phone = $cliente->phone;
        $this->status = $cliente->status ? 'Activo' : 'Inactivo';
        $this->cliente_id = $cliente->id;

        $this->showModal = true;
        $this->editMode = false;
        $this->deleteMode = false;
    }

    public function create()
    {
        $this->reset(['name', 'address',  'mail', 'phone', 'status', 'cif', 'cliente_id', 'editMode', 'deleteMode']);
        $this->showModal = true;
        $this->editMode = false;
        $this->deleteMode = false;
    }

    public function store()
    {
        if (!$this->isAdmin) {
            abort(403);
        }

        $this->validate($this->reglasCliente());

        Cliente::create([
            'name'      => $this->name,
            'address'   => $this->address,
            'mail'      => $this->mail,
            'phone'     => $this->phone,
            'cif'       => $this->cif,
        ]);


        $this->showModal = false;
        session()->flash('success', __('Client created successfully'));
    }


    public function edit($id)
    {
        $cliente = Cliente::findOrFail($id);
        $this->cliente_id = $cliente->id;
        $this->name = $cliente->name;
        $this->address = $cliente->address;
        $this->cif = $cliente->cif;
        $this->mail = $cliente->mail;
        $this->phone = $cliente->phone;
        $this->status = $cliente->status;

        $this->showModal = true;
        $this->editMode = true;
        $this->deleteMode = false;
    }

    public function update()
    {
        if (!$this->isAdmin) {
            abort(403);
        }

        try {

            $cliente = Cliente::findOrFail($this->cliente_id);
            $this->validate($this->reglasCliente($this->cliente_id));

            $cliente->update([
                'name'      => $this->name,
                'address'   => $this->address,
                'mail'      => $this->mail,
                'phone'     => $this->phone,
                'cif'       => $this->cif,
            ]);

            $this->showModal = false;

            session()->flash('success',  __('Client updated successfully'));
        } catch (\Throwable $th) {
            Log::error('Error en update: ' . $th->getMessage());
            // Log::info('el id del cliete : ' . $this->cliente_id);
            throw $th;
        }
    }

    public function cambiar_status(Cliente $cliente)
    {
        if (!$this->isAdmin) {
            abort(403);
        }

        $cliente->status = !$cliente->status;
        $cliente->save();

        session()->flash('success', (__('Status updated successfully')));
    }

    public function confirmDelete($id)
    {
        $this->show($id);
        $this->cliente_id = $id;
        $this->deleteMode = true; // Activar modo eliminar
    }

    public function destroy($id=null)
    {
        if (!$this->isAdmin) {
            abort(403);
        }

        try {

            $cliente = Cliente::findOrFail($this->cliente_id);

            if ($cliente->tareas()->count() > 0) {
                $this->closeModal();
                session()->flash('error', __('The client cannot be deleted because he has tasks assigned to him, you can change the status'));
                return;
            }

            $cliente->delete();

            $this->closeModal();

            session()->flash('success',  __('Client deleted successfully'));
        } catch (\Throwable $th) {
            Log::error('Error al eliminar destroy cliente: ' . $th->getMessage());
            throw $th;
        }
    }

    private function reglasCliente($clienteId = null)
    {
        return (new ClienteRequest())->rules($clienteId);
    }

    public function closeModal()
    {
        $this->deleteMode = false;
        $this->showModal = false; // Cierra el modal
        $this->showCliente = null;
    }

    public function updatingfiltroEstatus()
    {
        $this->resetPage();
    }
}
