<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <flux:heading size="2xl">{{ __('Task List') }}</flux:heading>
        <div class="flex gap-2">
            <button wire:click="create"
                class="px-4 py-2 bg-blue-400 text-white rounded hover:bg-blue-600 transition">{{ __('New Task') }}</button>
            <button
                class="px-4 py-2 bg-zinc-400 text-white rounded hover:bg-zinc-500 transition">{{ __('Create Task Group') }}</button>
            <button wire:click="exportExcel"
                class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700 transition">{{ __('Export Excel') }}
            </button>

        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-5 gap-5 mb-6">
        <flux:input wire:model.live="search" type="search" icon="magnifying-glass"
            placeholder="{{ __('Search task or date...') }}" />
        <flux:select wire:model.live="filtroEstatus">
            <option value="">{{ __('All Statuses') }}</option>
            <option value="Pendiente">{{ __('Pending') }}</option>
            <option value="Iniciada">{{ __('Started') }}</option>
            <option value="Completada">{{ __('Completed') }}</option>
        </flux:select>
        <flux:select wire:model.live="filtroEmpleado">
            <option value="">{{ __('All Employees') }}</option>
            @foreach ($allEmpleados as $empleado)
                <option value="{{ $empleado->id }}">{{ $empleado->name }}</option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="filtroCliente">
            <option value="">{{ __('All Clients') }}</option>
            @foreach ($allClientes as $cliente)
                <option value="{{ $cliente->id }}">{{ $cliente->name }}</option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="perPage">
            <option value="5">5 {{ __('per page') }}</option>
            <option value="10">10 {{ __('per page') }}</option>
            <option value="25">25 {{ __('per page') }}</option>
            <option value="50">50 {{ __('per page') }}</option>
        </flux:select>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded shadow">
            <thead>
                <tr class="bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200">
                    <th wire:click="ordenarPor('id')">#</th>
                    <th wire:click="ordenarPor('tarea')">{{ __('Task') }}</th>
                    <th wire:click="ordenarPor('estatus')">{{ __('Status') }}</th>
                    <th wire:click="ordenarPor('fecha')">{{ __('Date') }}</th>
                    <th wire:click="ordenarPor('horas')">{{ __('Hours') }}</th>
                    <th wire:click="ordenarPor('user_id')">{{ __('Employee') }}</th>
                    <th wire:click="ordenarPor('cliente_id')">{{ __('Client') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tareas_pag as $i => $tarea)
                    <tr class="border-t border-zinc-200 dark:border-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-800">
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
                                <button wire:click="confirmDelete({{ $tarea->id }})"
                                    class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs">Eliminar</button>
                                {{--  onclick="return confirm('¿Realmente desea borrar la tarea:  {{ $tarea->tarea }} ?')" --}}
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
        <div class="mt-2 flex justify-between items-center">
            <span>
                {{ $tareas_pag->count() }} {{ __('of') }} {{ $tareas_pag->total() }} {{ __('total tasks') }}
            </span>
            <span>
                {{ $tareas_pag->links() }}
            </span>
        </div>
    </div>

    {{-- colocando el modal --}}

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-xl w-full max-w-lg p-6 relative">
                <button wire:click="closeModal"
                    class="absolute top-2 right-2 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 text-2xl font-bold focus:outline-none">
                    &times;
                </button>

                @if ($editMode)
                    <h2 class="text-xl font-bold mb-4 text-zinc-800 dark:text-zinc-100">{{ __('Edit Task') }} </h2>
                @elseif($showTarea)
                    <h2 class="text-xl font-bold mb-4 text-zinc-800 dark:text-zinc-100">
                        @if ($deleteMode)
                            {{ __('Confirm Task Deletion') }}
                        @else
                            {{ __('Task Details') }}
                        @endif
                    </h2>

                    <div class="mb-3">
                        <flux:textarea readonly wire:model="tarea" rows="2" maxlength="255"
                                label="{{ __('Task') }}"  />
                    </div>
                    <div class="mb-3">
                        <label class="block text-zinc-700 dark:text-zinc-200 mb-1">{{ __('Date') }}</label>
                        <input type="date" wire:model="fecha" readonly
                            class="w-full rounded border bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200" />
                    </div>
                    <div class="mb-3">
                        <label class="block text-zinc-700 dark:text-zinc-200 mb-1">{{ __('Hours') }}</label>
                        <input type="number" wire:model="horas" readonly
                            class="w-full rounded border bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200" />
                    </div>
                    <div class="mb-3">
                        <label class="block text-zinc-700 dark:text-zinc-200 mb-1">{{ __('Status') }}</label>
                        <input type="text" wire:model="estatus" readonly
                            class="w-full rounded border bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200" />
                    </div>
                    <div class="mb-3">
                        <label class="block text-zinc-700 dark:text-zinc-200 mb-1">{{ __('Client') }}</label>
                        <input type="text" value="{{ optional($allClientes->find($cliente_id))->name }}" readonly
                            class="w-full rounded border bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200" />
                    </div>
                    <div class="mb-3">
                        <label class="block text-zinc-700 dark:text-zinc-200 mb-1">{{ __('Employee(s)') }}</label>
                        <input type="text"
                            value="@if (is_array($user_id)) {{ $allEmpleados->whereIn('id', $user_id)->pluck('name')->join(', ') }}@else{{ optional($allEmpleados->find($user_id))->name }} @endif"
                            readonly
                            class="w-full rounded border bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200" />
                    </div>
                    @if ($deleteMode)
                        <div class="text-red-600 font-semibold mb-4">
                            {{ __('Are you sure you want to delete this task?') }}
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="destroy({{ $tarea_id }})"
                                class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">{{ __('Delete') }}</button>
                            <button wire:click="closeModal"
                                class="px-4 py-2 bg-zinc-500 text-white rounded hover:bg-zinc-600">{{ __('Cancel') }}</button>
                        </div>
                    @else
                        <button wire:click="closeModal"
                            class="mt-4 px-4 py-2 bg-zinc-500 text-white rounded hover:bg-zinc-600">{{ __('Close') }}</button>
                    @endif
                @else
                    <h2 class="text-xl font-bold mb-4 text-zinc-800 dark:text-zinc-100">{{ __('New Task') }}</h2>
                @endif

                @if (!$showTarea)
                    <form wire:submit.prevent="{{ $editMode ? 'update' : 'store' }}">
                        <div class="mb-3">
                            <flux:textarea autofocus wire:model="tarea" rows="2" maxlength="255"
                                label="{{ __('Task') }}" placeholder="{{ __('Task Description') }}" />
                            @error('tarea')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="block text-zinc-700 dark:text-zinc-200 mb-1">{{ __('Date') }}</label>
                            <input type="date" wire:model="fecha"
                                class="w-full rounded border focus:ring-2 focus:ring-blue-500" />
                            @error('fecha')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="block text-zinc-700 dark:text-zinc-200 mb-1">{{ __('Hours') }}</label>
                            <input type="number" wire:model="horas" min="1" max="10"
                                class="w-full rounded border focus:ring-2 focus:ring-blue-500" />
                            @error('horas')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="block text-zinc-700 dark:text-zinc-200 mb-1">{{ __('Client') }}</label>
                            <select wire:model="cliente_id"
                                class="w-full rounded border focus:ring-2 focus:ring-blue-500">
                                <option value="">{{ __('Select Client') }}</option>
                                @foreach ($allClientes as $cliente)
                                    <option value="{{ $cliente->id }}">{{ $cliente->name }}</option>
                                @endforeach
                            </select>
                            @error('cliente_id')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="block text-zinc-700 dark:text-zinc-200 mb-1">{{ __('Employee(s)') }}</label>
                            @if ($editMode)
                                <select wire:model="user_id"
                                    class="w-full rounded border focus:ring-2 focus:ring-blue-500">
                                    <option value="">{{ __('Select one employee') }}</option>
                                    @foreach ($allEmpleados as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <select wire:model="user_id" multiple
                                    class="w-full rounded border focus:ring-2 focus:ring-blue-500">
                                    <option value="">{{ __('Select one or more employees') }}</option>
                                    @foreach ($allEmpleados as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            @endif

                            @error('user.*')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="flex gap-2 mt-4">
                            <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">{{ __('Save') }}
                            </button>
                            <button type="button" wire:click="closeModal"
                                class="px-4 py-2 bg-zinc-500 text-white rounded hover:bg-zinc-600">{{ __('Back') }}</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    @endif
    {{-- fin del modal --}}


    @if (session()->has('success'))
        <div class="mt-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif
</div>
