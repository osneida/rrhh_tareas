<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <flux:heading size="xl">{{ __('My Days') }}</flux:heading>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
        <flux:input wire:model.live="search" type="search" icon="magnifying-glass"
            placeholder="{{ __('Search task or date...') }}" />

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
                    @foreach (['id' => '#', 'Fecha' => __('Date'), 'hora_inicio' => __('Start Hour'), 'hora_fin' => __('End Hours'), 'total_horas' => __('Total Hours'), 'cliente_name' => __('Client'), 'tarea' => __('Task')] as $field => $label)
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

                </tr>
            </thead>
            <tbody>
                @forelse ($jornadaslb as $jornada)
                   <tr class="border-t border-zinc-200 dark:border-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-800">
                        <td class="px-4">{{ $loop->iteration }}</td>
                        <td class="whitespace-nowrap">{{ $jornada->fecha }}</td>
                        <td class="px-4">{{ $jornada->hora_inicio }}</td>
                        <td class="px-4">{{ $jornada->hora_fin }}</td>
                        <td class="px-4">{{ $jornada->horas_transcurridas }} :
                            {{ $jornada->minutos_transcurridos }}</td>
                        <td class="px-4">{{ $jornada->tarea->cliente->name ?? '-' }}</td>
                        <td class="px-4">{{ $jornada->tarea->tarea ?? '-' }}</td>

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

        <div class="mt-2 flex justify-between items-center">
            <span>
                {{ $jornadaslb->count() }} {{ __('of') }} {{ $jornadaslb->total() }}
                {{ __('total tasks') }}
            </span>
            <span>
                {{ $jornadaslb->links() }}
            </span>
        </div>

    </div>
</div>
