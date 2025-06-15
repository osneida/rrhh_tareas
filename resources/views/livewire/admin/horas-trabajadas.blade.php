<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <flux:heading size="xl">{{ __('Total hours employed per month') }}</flux:heading>
        <div class="flex gap-2">
            <button wire:click="exportExcel"
                class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700 transition">{{ __('Export Excel') }}
            </button>

        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
        <flux:input wire:model.live="search" type="search" icon="magnifying-glass"
            placeholder="{{ __('Search month ...') }}" />

        <flux:select wire:model.live="filtroEmpleado">
            <option value="">{{ __('All Employees') }}</option>
            @foreach ($allEmpleados as $empleado)
                <option value="{{ $empleado->id }}">{{ $empleado->name }}</option>
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
                    @foreach (['id' => '#', 'mes' => __('Month'), 'user_name' => __('Employee'), 'total_horas' => __('Total Hours')] as $field => $label)
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
                @forelse ($tiempo_transcurrido as $jornada)
                    <tr class="border-t border-zinc-200 dark:border-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-800">
                        <td class="px-4">{{ $loop->iteration }}</td>
                        <td class="whitespace-nowrap">{{ $jornada->mes }}</td>
                        <td class="px-4">{{ $jornada->user_name ?? '-' }}</td>
                        <td class="px-4">{{ $jornada->tiempo_transcurrido }}</td>

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
                {{ $tiempo_transcurrido->count() }} {{ __('of') }} {{ $tiempo_transcurrido->total() }}
                {{ __('Total employees, month') }}
            </span>
            <span>
                {{ $tiempo_transcurrido->links() }}
            </span>
        </div>

    </div>
</div>
