<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <flux:heading size="xl">{{ __('Working day') }}</flux:heading>
        <div class="flex gap-2">
            <button wire:click="exportExcel"
                class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700 transition">{{ __('Export Excel') }}
            </button>

        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-5 gap-5 mb-6">
        <flux:input wire:model.live="search" type="search" icon="magnifying-glass"
            placeholder="{{ __('Search task or date...') }}" />
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
            @foreach ($paginacion as $page)
                <option value="{{ $page->value }}">{{ $page->label() }} {{ __('per page') }}</option>
            @endforeach
        </flux:select>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded shadow">
            <thead>
                <tr class="bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200">
                    @foreach (['id' => '#', 'user_id' => __('Employee'), 'Fecha' => __('Date'), 'hora_inicio' => __('Start Hour'), 'hora_fin' => __('End Hours'), 'cliente_id' => __('Client'), 'total_horas' => __('Total Hours'), 'tarea' => __('Task')] as $field => $label)
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
                @forelse ($jornadas as $jornada)
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <td class="px-4 py-2">{{ $jornada->id }}</td>
                        <td class="px-4 py-2">{{ $jornada->tarea->user->name ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $jornada->fecha }}</td>
                        <td class="px-4 py-2">{{ $jornada->hora_inicio }}</td>
                        <td class="px-4 py-2">{{ $jornada->hora_fin }}</td>
                        <td class="px-4 py-2">{{ $jornada->horas_transcurridas}} : {{$jornada->minutos_transcurridos }}</td>
                        <td class="px-4 py-2">{{ $jornada->tarea->cliente->name ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $jornada->tarea->tarea ?? '-' }}</td>
                        <td class="px-4 py-2">
                            @if ($isAdmin)
                                <button wire:click="edit({{ $jornada->id }})"
                                    class="px-2 py-1 bg-emerald-500 text-white rounded hover:bg-emerald-600 text-xs"
                                    title="{{ __('Edit') }}">
                                    <flux:icon.edit class="size-4" />
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center px-4 py-2 text-gray-500">
                            {{ __('No records found.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        {{--
        <div class="mt-2 flex justify-between items-center">
            <span>
                {{ $jornadas->count() }} {{ __('of') }} {{ $jornadas->total() }} {{ __('total tasks') }}
            </span>
            <span>
                {{ $jornadas->links() }}
            </span>
        </div>
--}}
    </div>



    @if (session()->has('success'))
        <div class="mt-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif
</div>
