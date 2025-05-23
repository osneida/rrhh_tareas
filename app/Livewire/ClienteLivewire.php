<?php

namespace App\Livewire;

use App\Http\Requests\ClienteRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\WithPagination;
use Livewire\Component;
use App\Models\Cliente;

class ClienteLivewire extends Component
{
    use WithPagination;

    public $name, $address, $cif, $mail, $phone, $status, $cliente_id;
    public $isAdmin = false;
    public $search = '';
    public $filtroEstatus = '';
    public $perPage = 10;

    public $ordenCampo = 'id';
    public $ordenDireccion = 'desc';

    public $showCliente;
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
                 Log::info(" valor del estatus ".$this->filtroEstatus);
            }

            $query->orderBy($this->ordenCampo, $this->ordenDireccion);

            $clientes = $query->paginate($this->perPage);

            return view('livewire.cliente-livewire', compact('clientes'));
        } catch (\Throwable $th) {
            Log::error('Error en update: ' . $th->getMessage());
            throw $th;
        }
    }

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
        $this->validate($this->reglasCliente());

        Cliente::create([
            'name'      => $this->name,
            'address'   => $this->address,
            'mail'      => $this->mail,
            'phone'     => $this->phone,
            'cif'       => $this->cif,
        ]);


        $this->showModal = false;
        session()->flash('success', 'Cliente creado correctamente.');
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
        $this->status = $cliente->status ? 'Activo' : 'Inactivo';


        $this->showModal = true;
        $this->editMode = true;
        $this->deleteMode = false;
    }

    public function update()
    {
        try {

            $cliente = Cliente::findOrFail($this->cliente_id);
            $this->validate($this->reglasCliente($this->cliente_id));

            $cliente->update([
                'name'      => $this->name,
                'address'   => $this->address,
                'mail'      => $this->mail,
                'phone'     => $this->phone,
                //'status'    => $this->status,
                'cif'       => $this->cif,
            ]);

            $this->showModal = false;

            session()->flash('success', 'Cliente actualizado correctamente.');
        } catch (\Throwable $th) {
            Log::error('Error en update: ' . $th->getMessage());
            // Log::info('el id del cliete : ' . $this->cliente_id);
            throw $th;
        }
    }

    public function cambiar_status($id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->update([
            'status'    => !$cliente->status,
        ]);
        session()->flash('success', 'status actualizado correctamente.');
    }

    public function confirmDelete($id)
    {
        $this->show($id);
        $this->deleteMode = true; // Activar modo eliminar
    }

    public function destroy($id)
    {
        try {
            $cliente = Cliente::findOrFail($id);
            $cliente->delete();

            $this->closeModal();

            session()->flash('success', 'Cliente eliminado correctamente.');
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
}
