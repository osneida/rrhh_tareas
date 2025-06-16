<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <flux:heading size="xl">{{ __('Employees List') }}</flux:heading>
        <div class="flex gap-2">
            <button wire:click="create"
                class="px-4 py-2 bg-blue-400 text-white rounded hover:bg-blue-600 transition">{{ __('New employee') }}</button>
        </div>
    </div>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5 mb-4">
        <div class="flex gap-4">

            <flux:input wire:model.live="search" type="search" icon="magnifying-glass"
                placeholder="{{ __('Search per name o email ...') }}" />

            <select wire:model.live="status" class="border rounded ml-20 px-2 py-1">
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
        <table class="min-w-full bg-white border rounded shadow">
            <thead>
                <tr class="bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200">
                    @foreach (['id' => '#', 'name' => 'Nombre', 'email' => 'Email', 'status' => 'Activo', 'role' => 'Rol'] as $field => $label)
                        <th class="text-left px-4 py-2 cursor-pointer" wire:click="ordenarPor('{{ $field }}')">
                            {{ $label }}
                            @if ($ordenCampo === $field)
                                <span>
                                    @if ($ordenDireccion === 'asc')
                                        ▲
                                    @else
                                        ▼
                                    @endif
                                </span>
                            @endif
                        </th>
                    @endforeach
                    <th class="text-left px-4 py-2">{{ __('Actions') }}</th>
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
                                    class="px-2 py-1 bg-blue-400 text-white rounded hover:bg-blue-500 text-xs"
                                    title="{{ __('Detail') }}">
                                    <flux:icon.detail class="size-4" />
                                </button>
                                <button wire:click="edit({{ $user->id }})"
                                    class="px-2 py-1 bg-emerald-500 text-white rounded hover:bg-emerald-600 text-xs"
                                    title="{{ __('Edit') }}">
                                    <flux:icon.edit class="size-4" />
                                </button>
                                <button wire:click="confirmDelete({{ $user->id }})"
                                    class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs"
                                    title="{{ __('Delete') }}">
                                    <flux:icon.delete class="size-4" />
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-2 text-center text-gray-500"> {{ __('No records found.') }}</td>
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
    {{-- colocando el modal --}}

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-xl w-full max-w-lg p-6 relative">
                <button wire:click="closeModal"
                    class="absolute top-2 right-2 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 text-2xl font-bold focus:outline-none">
                    &times;
                </button>
                <h2 class="text-xl font-bold mb-4 text-zinc-800 dark:text-zinc-100">
                    {{ __('Confirm Employee Deletion') }}
                </h2>
                <div class="mb-3">
                    <label class="block text-zinc-700 dark:text-zinc-200 mb-1"
                        maxlength="100">{{ __('Name') }}</label>
                    <input readonly type="text" wire:model="name"
                        class="w-full rounded border bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200" />
                </div>
                <div class="mb-3">
                    <label class="block text-zinc-700 dark:text-zinc-200 mb-1"
                        maxlength="100">{{ __('Email') }}</label>
                    <input readonly type="text" wire:model="email"
                        class="w-full rounded border bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200" />
                </div>
                <label class="block text-zinc-700 dark:text-zinc-200 mb-1">{{ __('Role') }}</label>
                <div class="mb-3 flex flex-row gap-6">
                    <input readonly type="text" wire:model="role"
                        class="w-full rounded border bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200" />
                </div>

                <div class="text-red-600 font-semibold mb-4">
                    {{ __('Are you sure you want to delete this employee?') }}
                </div>
                <div class="flex gap-2">
                    <button wire:click="destroy({{ $user_id }})"
                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">{{ __('Delete') }}</button>
                    <button wire:click="closeModal"
                        class="px-4 py-2 bg-zinc-500 text-white rounded hover:bg-zinc-600">{{ __('Cancel') }}</button>
                </div>

            </div>
        </div>
    @endif
    {{-- fin del modal --}}

</div>
