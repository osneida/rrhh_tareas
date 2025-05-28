<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <flux:heading size="xl">{{ __('Client List') }}</flux:heading>
        <div class="flex gap-2">
            <button wire:click="create"
                class="px-4 py-2 bg-blue-400 text-white rounded hover:bg-blue-600 transition">{{ __('New Client') }}</button>
        </div>
    </div>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5 mb-4">
        <div class="flex gap-4">

            <flux:input wire:model.live="search" type="search" icon="magnifying-glass"
                placeholder="{{ __('Search ...') }}" />

            <select wire:model.live="filtroEstatus" class="border rounded ml-20 px-2 py-1">
                <option value="">{{ __('All Statuses') }}</option>
                @foreach ($selectStatus as $statu)
                    <option value="{{ $statu->value }}">{{ $statu->label() }}</option>
                @endforeach
            </select>
            <select wire:model.live="perPage" class="border rounded ml-20 px-2 py-1">
                @foreach ($paginacion as $page)
                    <option value="{{ $page->value }}">{{ $page->label() }} {{ __('per page') }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded shadow">
            <thead>
                <tr class="bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200">
                    <th class="text-left px-4 py-2 cursor-pointer" wire:click="ordenarPor('id')"># </th>
                    <th class="text-left px-4 py-2 cursor-pointer" wire:click="ordenarPor('name')">{{ __('Client') }}
                    </th>
                    <th class="text-left px-4 py-2 cursor-pointer" wire:click="ordenarPor('address')">
                        {{ __('address') }}</th>
                    <th class="text-left px-4 py-2 cursor-pointer" wire:click="ordenarPor('cif')">{{ __('cif') }}
                    </th>
                    <th class="text-left px-4 py-2 cursor-pointer" wire:click="ordenarPor('mail')">{{ __('mail') }}
                    </th>
                    <th class="text-left px-4 py-2 cursor-pointer" wire:click="ordenarPor('phone')">{{ __('phone') }}
                    </th>
                    <th class="text-left px-4 py-2 cursor-pointer" wire:click="ordenarPor('status')">
                        {{ __('status') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>

                @forelse($clientes as $cliente)
                    <tr wire:key="cliente-{{ $cliente->id }}"
                        class="border-t border-zinc-200 dark:border-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-800">
                        <td class="px-3 py-2">{{ $loop->iteration }}</td>
                        <td class="px-3 py-2">{{ $cliente->name }}</td>
                        <td class="px-3 py-2">{{ $cliente->address }}</td>
                        <td class="px-3 py-2">{{ $cliente->cif }}</td>
                        <td class="px-3 py-2">{{ $cliente->mail }}</td>
                        <td class="px-3 py-2">{{ $cliente->phone }}</td>
                        <td class="px-3 py-2">
                            <flux:fieldset>
                                <div class="space-y-3">
                                    @if ($cliente->status == 1)
                                        <flux:switch checked wire:click="cambiar_status({{ $cliente->id }})"
                                            title="Activo" />
                                    @else
                                        <flux:switch wire:click="cambiar_status({{ $cliente->id }})"
                                            title="Inactivo" />
                                    @endif
                                </div>
                            </flux:fieldset>
                        </td>
                        <td class="px-3 py-2 flex gap-1">
                            <button wire:click="show({{ $cliente->id }})"
                                class="px-2 py-1 bg-emerald-500 text-white rounded hover:bg-emerald-600 text-xs">Ver</button>
                            @if ($isAdmin)
                                <button wire:click="edit({{ $cliente->id }})"
                                    class="px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-xs">Editar</button>
                                <button wire:click="confirmDelete({{ $cliente->id }})"
                                    class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs">Eliminar</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-zinc-500">No hay clientes registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-2 flex justify-between items-center">
            <span>
                {{ $clientes->count() }} {{ __('of') }} {{ $clientes->total() }} {{ __('total Clients') }}
            </span>
            <span>
                {{ $clientes->links() }}
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
                    <h2 class="text-xl font-bold mb-4 text-zinc-800 dark:text-zinc-100">{{ __('Edit Client') }}</h2>
                @elseif($showCliente)
                    <h2 class="text-xl font-bold mb-4 text-zinc-800 dark:text-zinc-100">
                        @if ($deleteMode)
                            {{ __('Confirm Client Deletion') }}
                        @else
                            {{ __('Client Details') }}
                        @endif
                    </h2>

                    <div class="mb-3">
                        <label class="block text-zinc-700 dark:text-zinc-200 mb-1">{{ __('Name') }}</label>
                        <input type="text" wire:model="name" readonly
                            class="w-full rounded border bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200" />
                    </div>
                    <div class="mb-3">
                        <flux:textarea readonly wire:model="address" rows="2" maxlength="255"
                            label="{{ __('Address') }}" />
                    </div>
                    <div class="mb-3">
                        <label class="block text-zinc-700 dark:text-zinc-200 mb-1">{{ __('CIF') }}</label>
                        <input type="text" wire:model="cif" readonly
                            class="w-full rounded border bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200" />
                    </div>

                    <div class="mb-3">
                        <label class="block text-zinc-700 dark:text-zinc-200 mb-1">{{ __('Email') }}</label>
                        <input type="text" wire:model="mail" readonly
                            class="w-full rounded border bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200" />
                    </div>
                    <div class="mb-3">
                        <label class="block text-zinc-700 dark:text-zinc-200 mb-1">{{ __('Phone') }}</label>
                        <input type="text" wire:model="phone" readonly
                            class="w-full rounded border bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200" />
                    </div>
                    <div class="mb-3">
                        <label class="block text-zinc-700 dark:text-zinc-200 mb-1">{{ __('Status') }}</label>
                        <input type="text" wire:model="status" readonly
                            class="w-full rounded border bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200" />
                    </div>
                    @if ($deleteMode)
                        <div class="text-red-600 font-semibold mb-4">
                            {{ __('Are you sure you want to delete this client?') }}
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="destroy({{ $cliente_id }})"
                                class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">{{ __('Delete') }}</button>
                            <button wire:click="closeModal"
                                class="px-4 py-2 bg-zinc-500 text-white rounded hover:bg-zinc-600">{{ __('Cancel') }}</button>
                        </div>
                    @else
                        <button wire:click="closeModal"
                            class="mt-4 px-4 py-2 bg-zinc-500 text-white rounded hover:bg-zinc-600">{{ __('Close') }}</button>
                    @endif
                @else
                    <h2 class="text-xl font-bold mb-4 text-zinc-800 dark:text-zinc-100">{{ __('New Client') }}</h2>
                @endif

                @if (!$showCliente)
                    <form wire:submit.prevent="{{ $editMode ? 'update' : 'store' }}">
                        <div class="mb-3">
                            <flux:textarea autofocus wire:model="name" rows="2" maxlength="45"
                                label="{{ __('Client') }}" placeholder="{{ __('Name Client') }}" />
                            @error('name')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <flux:textarea wire:model="address" rows="2" maxlength="255"
                                label="{{ __('Address') }}" />
                            @error('address')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="block text-zinc-700 dark:text-zinc-200 mb-1">{{ __('CIF') }}</label>
                            <input type="text" wire:model="cif"
                                class="w-full rounded border bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200" />
                            @error('cif')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="block text-zinc-700 dark:text-zinc-200 mb-1">{{ __('Email') }}</label>
                            <input type="text" wire:model="mail"
                                class="w-full rounded border bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200" />
                            @error('mail')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="block text-zinc-700 dark:text-zinc-200 mb-1">{{ __('Phone') }}</label>
                            <input type="text" wire:model="phone"
                                class="w-full rounded border bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200" />
                            @error('phone')
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

    @if (session()->has('error'))
        <div class="mt-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
    @endif
</div>
