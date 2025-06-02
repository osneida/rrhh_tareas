<div class="p-6">
    @if ($dashboard)
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <flux:heading size="xl">{{ __('Tasks by Group') }}</flux:heading>
            <div class="flex gap-2">
                <button wire:click="create"
                    class="px-4 py-2 bg-blue-400 text-white rounded hover:bg-blue-600 transition">{{ __('New Tasks') }}</button>
            </div>
        </div>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5 mb-4">
            <div class="flex gap-4">

                <flux:input wire:model.live="search" type="search" icon="magnifying-glass"
                    placeholder="{{ __('Search ...') }}" />

                <select wire:model.live="perPage" class="border rounded ml-20 px-2 py-1">
                    @foreach ($paginacion as $page)
                        <option value="{{ $page->value }}">{{ $page->label() }} {{ __('per page') }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border rounded shadow">
                <thead>
                    <tr class="bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200">
                        @foreach (['id' => '#', 'descripcion' => 'Descripción', 'created_at' => 'Fecha Creación'] as $field => $label)
                            <th class="text-left px-4 py-2 cursor-pointer" wire:click="sortBy('{{ $field }}')">
                                {{ $label }}
                                @if ($sortField === $field)
                                    <span>
                                        @if ($sortDirection === 'asc')
                                            ▲
                                        @else
                                            ▼
                                        @endif
                                    </span>
                                @endif
                            </th>
                        @endforeach
                        <th class="text-left px-4 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tareas_grupo as $tarea)
                        <tr wire:key="tarea-{{ $tarea->id }}"
                            class="border-t border-zinc-200 dark:border-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-800">
                            <td class="px-4 py-2">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2">{{ $tarea->descripcion }}</td>
                            <td class="px-4 py-2">{{ $tarea->created_at }}</td>
                            <td class="px-4 py-2">
                                @if ($isAdmin)
                                    <button wire:click="show({{ $tarea->id }})"
                                        class="px-2 py-1 bg-emerald-500 text-white rounded hover:bg-emerald-600 text-xs">Ver</button>
                                    <button wire:click="confirmDelete({{ $tarea->id }})"
                                        class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs">Eliminar</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-2 text-center text-gray-500">No hay Tareas</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


        <div class="mt-2 flex justify-between items-center">
            <span>
                {{ $tareas_grupo->count() }} {{ __('of') }} {{ $tareas_grupo->total() }}
                {{ __('total tareas') }}
            </span>
            <span>
                {{ $tareas_grupo->links() }}
            </span>
        </div>

    @endif
    {{-- colocando el modal --}}

    @if ($createMode)

        <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-xl w-full max-w-2xl p-6 relative">
            <h2 class="text-xl font-bold mb-4 text-zinc-800 dark:text-zinc-100">{{ __('New Group Task') }}</h2>
            @if (!$showTarea)
                <form wire:submit.prevent="{{ $editMode ? 'update' : 'store' }}">

                    <div class="mb-3">
                        <flux:textarea autofocus wire:model="descripcion" rows="2" maxlength="255"
                            label="{{ __('Group Description') }}" placeholder="{{ __('Group Description') }}" />
                        @error('descripcion')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    =============
                    <h2 class="text-xl font-bold mb-4 text-zinc-800 dark:text-zinc-100">{{ __('New Tasks') }}</h2>

                    <div class="mb-3">
                        <flux:textarea wire:model="tarea" rows="2" maxlength="255" label="{{ __('Task') }}"
                            placeholder="{{ __('Task Description') }}" />
                        @error('tarea')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="block text-zinc-700 dark:text-zinc-200 mb-1">{{ __('Start date') }}</label>
                        <input type="date" wire:model="fecha_inicio"
                            class="w-full rounded border focus:ring-2 focus:ring-blue-500" />
                        @error('fecha_inicio')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="block text-zinc-700 dark:text-zinc-200 mb-1">{{ __('End date') }}</label>
                        <input type="date" wire:model="fecha_fin"
                            class="w-full rounded border focus:ring-2 focus:ring-blue-500" />
                        @error('fecha_fin')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="block text-zinc-700 dark:text-zinc-200 mb-1">{{ __('Select the days') }} </label>
                        <div class="flex">

                            @foreach ($losDias as $di)
                                @php
                                    $checkboxId = 'dia-checkbox-' . $di->value;
                                @endphp
                                <div class="flex items-center me-4">
                                    <input id="{{ $checkboxId }}" wire:model="dias" type="checkbox"
                                        value="{{ $di->value }}"
                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="{{ $checkboxId }}"
                                        class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">{{ $di->label() }}</label>
                                </div>
                            @endforeach


                        </div>
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
                        <select wire:model="cliente_id" class="w-full rounded border focus:ring-2 focus:ring-blue-500">
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
                            <select wire:model="user_id" class="w-full rounded border focus:ring-2 focus:ring-blue-500">
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

    @endif
    {{-- fin del modal --}}

    @if ($showTarea)
        <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-xl w-full  p-6 relative">
            <h2 class="text-xl font-bold mb-4 text-zinc-800 dark:text-zinc-100">{{ __('Task Group Details') }}</h2>
            <div class="mb-3">
                <p><strong>{{ __('Description') }} :</strong> {{ $descripcion }}</p>
                <p><strong>{{ __('Start Date') }} :</strong> {{ $fecha_inicio }} | <strong>{{ __('End Date') }}
                        :</strong> {{ $fecha_fin }}</p>
                <p></p>
                <strong>{{ __('Days') }} :</strong>
                {{ implode(', ', collect($dias)->map(fn($d) => \App\Enums\DiasEnum::tryFrom($d)?->label() ?? $d)->toArray()) }}
                <p><strong>{{ __('Client') }} :</strong>
                    {{ $tareasPaginadas[0]->cliente ? $tareasPaginadas[0]->cliente->name : 'N/A' }}</p>

            </div>
            <div class="mb-3">
                <div class="overflow-x-auto">
                    <h2 class="text-xl font-bold mb-4 text-zinc-800 dark:text-zinc-100">{{ __('Task List') }}</h2>
                    <table
                        class="min-w-full bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded shadow">
                        <thead>
                            <tr class="bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200">
                                <th class="px-3 text-center cursor-pointer" wire:click="ordenarPor('id')">ID</th>
                                <th class="px-3 text-left cursor-pointer" wire:click="ordenarPor('tarea')">
                                    {{ __('Task') }}</th>
                                <th class="text-left px-4 cursor-pointer" wire:click="ordenarPor('estatus')">
                                    {{ __('Status') }}</th>
                                <th class="text-left px-4 cursor-pointer" wire:click="ordenarPor('fecha')">
                                    {{ __('Date') }}</th>
                                <th class="text-left px-4 cursor-pointer" wire:click="ordenarPor('horas')">
                                    {{ __('Hours') }}</th>
                                <th class="text-left px-4 cursor-pointer" wire:click="ordenarPor('user_id')">
                                    {{ __('Employee') }}</th>

                                <th class="text-left px-4 cursor-pointer">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tareasPaginadas as $tarea)
                                <tr
                                    class="border-t border-zinc-200 dark:border-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-800">
                                    <td class="px-3 py-2">{{ $tarea->id }}</td>
                                    <td class="px-3 py-2">{{ $tarea->tarea }}</td>
                                    <td class="px-3 py-2">{{ $tarea->estatus }}</td>
                                    <td class="px-3 py-2">{{ $tarea->fecha }}</td>
                                    <td class="px-3 py-2">{{ $tarea->horas }}</td>
                                    <td class="px-3 py-2">{{ $tarea->user->name ?? '-' }}</td>
                                    <td class="px-3 py-2 flex gap-1">

                                        @if ($isAdmin)
                                            <button wire:click="edit({{ $tarea->id }})"
                                                class="px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-xs">Editar</button>
                                            <button wire:click="confirmDelete({{ $tarea->id }})"
                                                class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs">Eliminar</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-zinc-500">No hay tareas
                                        registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    @if ($showTarea)
                        <div class="mt-2 flex justify-between items-center">
                            <span>
                                {{ $tareasPaginadas->count() }} {{ __('of') }} {{ $tareasPaginadas->total() }}
                                {{ __('total tareas') }}
                            </span>
                            <span>
                                {{ $tareasPaginadas->links() }}
                            </span>
                        </div>
                    @endif
                </div>

            </div>

            <button type="button" wire:click="closeModal"
                class="px-4 py-2 bg-zinc-500 text-white rounded hover:bg-zinc-600">{{ __('Back') }}</button>
        </div>
    @endif

    @if (session()->has('success'))
        <div class="mt-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mt-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
    @endif


</div>
