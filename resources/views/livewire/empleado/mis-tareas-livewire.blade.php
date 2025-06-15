<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <flux:heading size="xl">{{ __('My Tasks') }}</flux:heading>

    </div>
    <div class="grid grid-cols-1 md:grid-cols-5 gap-5 mb-6">
        <flux:input wire:model.live="search" type="search" icon="magnifying-glass"
            placeholder="{{ __('Search task or date...') }}" />
        <flux:select wire:model.live="filtroEstatus">
            <option value="">{{ __('All Statuses') }}</option>
            @foreach ($selectEstatusTarea as $statu)
                <option value="{{ $statu->value }}">{{ $statu->label() }}</option>
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
                    <th class="px-3 text-center cursor-pointer" wire:click="ordenarPor('id')">
                        @if ($ordenCampo === 'id')
                            <span>
                                @if ($ordenDireccion === 'asc')
                                    ▲
                                @else
                                    ▼
                                @endif
                            </span>
                        @endif #
                    </th>
                    <th class="px-3 text-left cursor-pointer" wire:click="ordenarPor('tarea')">
                        @if ($ordenCampo === 'tarea')
                            <span>
                                @if ($ordenDireccion === 'asc')
                                    ▲
                                @else
                                    ▼
                                @endif
                            </span>
                        @endif{{ __('Task') }}
                    </th>
                    <th class="text-left px-4 cursor-pointer" wire:click="ordenarPor('estatus')">

                        @if ($ordenCampo === 'estatus')
                            <span>
                                @if ($ordenDireccion === 'asc')
                                    ▲
                                @else
                                    ▼
                                @endif
                            </span>
                        @endif{{ __('Status') }}
                    </th>
                    <th class="text-left px-4 cursor-pointer" wire:click="ordenarPor('fecha')">
                        @if ($ordenCampo === 'fecha')
                            <span>
                                @if ($ordenDireccion === 'asc')
                                    ▲
                                @else
                                    ▼
                                @endif
                            </span>
                        @endif{{ __('Date') }}
                    </th>
                    <th class="text-left px-4 cursor-pointer" wire:click="ordenarPor('horas')">
                        @if ($ordenCampo === 'horas')
                            <span>
                                @if ($ordenDireccion === 'asc')
                                    ▲
                                @else
                                    ▼
                                @endif
                            </span>
                        @endif{{ __('Hours') }}
                    </th>

                    <th class="text-left px-4 cursor-pointer" wire:click="ordenarPor('cliente_name')">
                        @if ($ordenCampo === 'cliente_name')
                            <span>
                                @if ($ordenDireccion === 'asc')
                                    ▲
                                @else
                                    ▼
                                @endif
                            </span>

                        @endif{{ __('Client') }}
                    </th>

                    <th class="text-left px-4 cursor-pointer" wire:click="ordenarPor('observacion')">
                        @if ($ordenCampo === 'observacion')
                            <span>
                                @if ($ordenDireccion === 'asc')
                                    ▲
                                @else
                                    ▼
                                @endif
                            </span>
                        @endif{{ __('Observation') }}
                    </th>

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
                        <td class="px-3 py-2">{{ $tarea->cliente->name ?? '-' }}</td>
                        <td class="px-3 py-2">{{ $tarea->observacion ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-zinc-500"> {{ __('No records found.') }}</td>
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



</div>
