<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <h2 class="text-2xl font-bold text-zinc-800 dark:text-zinc-100">Listado de Tareas</h2>
        <div class="flex gap-2">
            <button wire:click="create"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Nueva Tarea</button>
            <button class="px-4 py-2 bg-zinc-500 text-white rounded hover:bg-zinc-600 transition">Crear Grupo de
                Tareas</button>
            <button class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700 transition">Imprimir
                Reporte</button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <input wire:model="search" type="text" class="w-full rounded border-zinc-300 focus:ring-2 focus:ring-blue-500"
            placeholder="Buscar tarea...">
        <select wire:model.live="filtroEstatus" class="w-full rounded border-zinc-300 focus:ring-2 focus:ring-blue-500">
            <option value="">Todos los Estatus</option>
            <option value="Pendiente">Pendiente</option>
            <option value="Iniciada">Iniciada</option>
            <option value="Completada">Completada</option>
        </select>
        <select wire:model.live="filtroEmpleado"
            class="w-full rounded border-zinc-300 focus:ring-2 focus:ring-blue-500">
            <option value="">Todos los Empleados</option>
            @foreach ($empleados as $empleado)
                <option value="{{ $empleado->id }}">{{ $empleado->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="filtroCliente" class="w-full rounded border-zinc-300 focus:ring-2 focus:ring-blue-500">
            <option value="">Todos los Clientes</option>
            @foreach ($clientes as $cliente)
                <option value="{{ $cliente->id }}">{{ $cliente->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded shadow">
            <thead>
                <tr class="bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200">
                    <th class="px-3 py-2 text-left cursor-pointer" wire:click="ordenarPor('id')">#</th>
                    <th class="px-3 py-2 text-left cursor-pointer" wire:click="ordenarPor('tarea')">Tarea</th>
                    <th class="px-3 py-2 text-left cursor-pointer" wire:click="ordenarPor('estatus')">Estatus</th>
                    <th class="px-3 py-2 text-left cursor-pointer" wire:click="ordenarPor('fecha')">Fecha</th>
                    <th class="px-3 py-2 text-left cursor-pointer" wire:click="ordenarPor('horas')">Horas</th>
                    <th class="px-3 py-2 text-left cursor-pointer" wire:click="ordenarPor('user_id')">Empleado</th>
                    <th class="px-3 py-2 text-left cursor-pointer" wire:click="ordenarPor('cliente_id')">Cliente</th>
                    <th class="px-3 py-2 text-left">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tareas_pag as $i => $tarea)
                    <tr class="border-t border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800">
                        <td class="px-3 py-2">{{ $i + 1 }}</td>
                        <td class="px-3 py-2">{{ $tarea->tarea }}</td>
                        <td class="px-3 py-2">{{ ucfirst($tarea->estatus) }}</td>
                        <td class="px-3 py-2">{{ $tarea->fecha }}</td>
                        <td class="px-3 py-2">{{ $tarea->horas }}</td>
                        <td class="px-3 py-2">{{ $tarea->user->name ?? '-' }}</td>
                        <td class="px-3 py-2">{{ $tarea->cliente->name ?? '-' }}</td>
                        <td class="px-3 py-2 flex gap-1">
                            <button wire:click="show({{ $tarea->id }})"
                                class="px-2 py-1 bg-emerald-500 text-white rounded hover:bg-emerald-600 text-xs">Ver</button>
                            @if ($isAdmin)
                                <button wire:click="edit({{ $tarea->id }})"
                                    class="px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-xs">Editar</button>
                                <button wire:click="destroy({{ $tarea->id }})"
                                    class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs"
                                    onclick="return confirm('¿Realmente desea borrar la tarea:  {{ $tarea->tarea }} ?')">Eliminar</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-zinc-500">No hay tareas registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-2  form-group d-flex justify-content-between align-items-center">
            <span class="float-left">
                {{ $tareas_pag->count() }} <label>de</label> {{ $tareas_pag->total() }} <label>total tareas</label>
            </span>
            <span class="float-right">
                {{ $tareas_pag->links() }}
            </span>
        </div>
    </div>



    {{-- colocando el modal --}}

@if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-xl w-full max-w-lg p-6 relative">
            <button wire:click="closeModal"
                class="absolute top-2 right-2 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 text-2xl font-bold focus:outline-none">
                &times;
            </button>

            @if($editMode)
                <h2 class="text-xl font-bold mb-4 text-zinc-800 dark:text-zinc-100">Editar Tarea</h2>
            @elseif($showTarea)
                <h2 class="text-xl font-bold mb-4 text-zinc-800 dark:text-zinc-100">Detalle de Tarea</h2>
                {{-- Mostrar detalles aquí --}}
                <button wire:click="closeModal"
                    class="mt-4 px-4 py-2 bg-zinc-500 text-white rounded hover:bg-zinc-600">Cerrar</button>
            @else
                <h2 class="text-xl font-bold mb-4 text-zinc-800 dark:text-zinc-100">Nueva Tarea</h2>
            @endif

            @if(!$showTarea)
                <form wire:submit.prevent="{{ $editMode ? 'update' : 'store' }}">
                    <div class="mb-3">
                        <label class="block text-zinc-700 dark:text-zinc-200 mb-1">Tarea</label>
                        <input type="text" wire:model="tarea"
                            class="w-full rounded border-zinc-300 focus:ring-2 focus:ring-blue-500" />
                        @error('tarea') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="block text-zinc-700 dark:text-zinc-200 mb-1">Fecha</label>
                        <input type="date" wire:model="fecha"
                            class="w-full rounded border-zinc-300 focus:ring-2 focus:ring-blue-500" />
                        @error('fecha') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="block text-zinc-700 dark:text-zinc-200 mb-1">Horas</label>
                        <input type="number" wire:model="horas" min="1" max="10"
                            class="w-full rounded border-zinc-300 focus:ring-2 focus:ring-blue-500" />
                        @error('horas') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="block text-zinc-700 dark:text-zinc-200 mb-1">Cliente</label>
                        <select wire:model="cliente_id"
                            class="w-full rounded border-zinc-300 focus:ring-2 focus:ring-blue-500">
                            <option value="">Seleccione Cliente</option>
                            @foreach($allClientes as $cliente)
                                <option value="{{ $cliente->id }}">{{ $cliente->name }}</option>
                            @endforeach
                        </select>
                        @error('cliente_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="block text-zinc-700 dark:text-zinc-200 mb-1">Empleado(s)</label>
                        <select wire:model="user_id" multiple
                            class="w-full rounded border-zinc-300 focus:ring-2 focus:ring-blue-500">
                            @foreach($allEmpleados as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @error('user.*') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex gap-2 mt-4">
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Guardar</button>
                        <button type="button" wire:click="closeModal"
                            class="px-4 py-2 bg-zinc-500 text-white rounded hover:bg-zinc-600">Volver</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endif
    {{-- fin del modal--}}



    @if (session()->has('message'))
        <div class="mt-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('message') }}
        </div>
    @endif
</div>
