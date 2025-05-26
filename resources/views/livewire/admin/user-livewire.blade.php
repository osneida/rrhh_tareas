<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <flux:heading size="xl">{{ __('Employees List') }}</flux:heading>
        <div class="flex gap-2">
            <button wire:click="create"
                class="px-4 py-2 bg-blue-400 text-white rounded hover:bg-blue-600 transition">{{ __('New employees') }}</button>
        </div>
    </div>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5 mb-4">
        <div class="flex gap-4">
            <input type="text" wire:model.live="search" placeholder="Buscar por nombre o email "
                class="border rounded px-2 py-1 " />
            <select wire:model.live="status" class="border rounded  ml-20 px-2 py-1">
                <option value="">{{  __('All Statuses')  }}</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
            <select wire:model.live="perPage" class="border rounded ml-20 py-1">
                <option value="5">5 {{ __('per page') }}</option>
                <option value="10">10 {{ __('per page') }}</option>
                <option value="25">25 {{ __('per page') }}</option>
                <option value="50">50 {{ __('per page') }}</option>
            </select>
        </div>


    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border rounded shadow">
            <thead>
                <tr class="bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200">
                    @foreach (['id' => '#', 'name' => 'Nombre', 'email' => 'Email', 'status' => 'Activo', 'role' => 'Rol'] as $field => $label)
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
                @forelse($users as $user)
                    <tr wire:key="user-{{ $user->id }}"
                        class="border-t border-zinc-200 dark:border-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-800">
                        <td class="px-4 py-2">{{ $loop->iteration }}</td>
                        <td class="px-4 py-2">{{ $user->name }}</td>
                        <td class="px-4 py-2">{{ $user->email }}</td>
                        <td class="px-4 py-2 text-left">
                            {{-- <input type="checkbox" wire:click="toggleStatus({{ $user->id }})"
                                {{ $user->status ? 'checked' : '' }} class="form-checkbox h-5 w-5 text-green-600"> --}}
                            @if ($user->status == 1)
                                <flux:switch checked wire:click="toggleStatus({{ $user->id }})" alert="activo"
                                    title="Activo" />
                            @else
                                <flux:switch wire:click="toggleStatus({{ $user->id }})" alert="inactivo"
                                    title="Inactivo" />
                            @endif
                        </td>
                        <td class="px-4 py-2">{{ $user->role }}</td>
                        <td class="px-4 py-2">
                            @if ($isAdmin)
                                <button wire:click="show({{ $user->id }})"
                                    class="px-2 py-1 bg-emerald-500 text-white rounded hover:bg-emerald-600 text-xs">Ver</button>
                                <button wire:click="edit({{ $user->id }})"
                                    class="px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-xs">Editar</button>
                                <button wire:click="confirmDelete({{ $user->id }})"
                                    class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs">Eliminar</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-2 text-center text-gray-500">No hay usuarios</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>


    <div class="mt-2 flex justify-between items-center">
        <span>
            {{ $users->count() }} {{ __('of') }} {{ $users->total() }} {{ __('total employee') }}
        </span>
        <span>
            {{ $users->links() }}
        </span>
    </div>



</div>
