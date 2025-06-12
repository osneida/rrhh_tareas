<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        @if ($isAdmin)
            <div class="grid auto-rows-min gap-4 md:grid-cols-4">

                <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200">
                    <div class="bg-cyan-700 rounded-xl shadow p-4 flex flex-col justify-between h-full">


                        <div class="grid auto-rows-min gap-2 md:grid-cols-2 text-white text-sm">

                            <div class="">Total Tareas:</div>
                            <div class="font-extrabold">{{ $totalTareas }}</div>


                        </div>

                        <div class="flex items-center justify-center mt-1">
                            <span class="text-white text-3xl">

                            </span>
                            <a href="#" class="text-white  hover:underline flex items-center gap-1">
                                Más info <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200">
                    <div class="bg-lime-700 rounded-xl shadow p-4 flex flex-col justify-between h-full">


                        <div class="grid auto-rows-min gap-4 md:grid-cols-4 text-white text-sm">
                            <div class="col-span-3 col-start-1 font-bold">Horas Pendientes:</div>
                            <div class="font-extrabold">{{ $horasPendientes }}</div>
                            <div class="col-span-3 col-start-1 font-bold">Horas Iniciadas:</div>
                            <div class="font-extrabold">{{ $horasIniciadas }}</div>
                            <div class="col-span-3 col-start-1 font-bold">Horas Finalizada:</div>
                            <div class="font-extrabold">{{ $horasCompletadas }}</div>


                        </div>

                        <div class="flex items-center justify-center mt-1">
                            <span class="text-white text-3xl">

                            </span>
                            <a href="#" class="text-white  hover:underline flex items-center gap-1">
                                Más info <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200">
                    <div class="bg-orange-400 rounded-xl shadow p-4 flex flex-col justify-between h-full">


                        <div class="grid auto-rows-min gap-3 md:grid-cols-3 text-white text-sm">

                            <div class="">Empleados Activos:</div>
                            <div class="col-span-2 col-start-3  font-extrabold">{{ $totalEmpleados }}</div>


                        </div>

                        <div class="flex items-center justify-center mt-1">
                            <span class="text-white text-3xl">

                            </span>
                            <a href="#" class="text-white  hover:underline flex items-center gap-1">
                                Más info <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200">
                    <div class="bg-teal-700 rounded-xl shadow p-4 flex flex-col justify-between h-full">


                        <div class="grid auto-rows-min gap-2 md:grid-cols-3 text-white text-sm">

                            <div class="">Clientes Activos:</div>
                            <div class="col-span-2 col-start-3  font-extrabold">{{ $totalClientes }}</div>


                        </div>

                        <div class="flex items-center justify-center mt-1">
                            <span class="text-white text-3xl">

                            </span>
                            <a href="#" class="text-white  hover:underline flex items-center gap-1">
                                Más info <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">


            @forelse ($tarea_hoy as $tarea)
                <div class="card">
                    <div class="card-header bg-orange-100 ">
                        <h3 class="card-title"> Jornada Laboral</h3> {{ $hoy }}
                        <h3> {{ $tarea->tarea }}</h3>
                        <p style="text-align: right;">{{ __('Client') }} :
                            {{ $tarea->cliente->name . ' | ' . $tarea->cliente->address }}</p>
                    </div>
                    <div class="card-body">
                        <button type="button" wire:click="guardar_hora_inicio({{ $tarea->id }})" :key="hora_inicio-{{$tarea->id}}"
                             {{ isset($enables_inicio[$tarea->id]) ? $enables_inicio[$tarea->id] : '' }} class="px-4 py-2 bg-blue-400 text-white rounded disabled:bg-gray-300 hover:bg-blue-500 transition">{{ __('Inicio Actividad') }}</button>
                        <button type="button" wire:click="guardar_hora_fin({{ $tarea->id }})" :key="hora_fin-{{$tarea->id}}"
                            {{ isset($enables_fin[$tarea->id]) ? $enables_fin[$tarea->id] : '' }} class="px-4 py-2 bg-red-400 text-white rounded disabled:bg-gray-300 hover:bg-red-500 transition">{{ __('Fin Actividad') }}
                        </button>

                    </div>
                    <input type="text" value="{{ isset($horas_inicio[$tarea->id]) ? $horas_inicio[$tarea->id] : '' }}"  readonly id="hora_inicio_text-{{$tarea->id}}"
                    class="w-1xl rounded border bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 mb-5" />

                    <input type="text" value="{{ isset($horas_fin[$tarea->id]) ? $horas_fin[$tarea->id] : '' }}" readonly id="hora_fin_text-{{$tarea->id}}"
                    class="w-1xl rounded border bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 mb-5" />

                </div>
            @empty
                <div class="card card-purple">
                    <div class="card-header">
                        <h3 class="card-title"> Sin Tarea para Hoy </h3>
                        <p style="text-align: right;"> {{ $hoy }} </p>
                    </div>

                    <div class="card-body">
                        <div class="container-fluid">

                        </div>
                    </div>
                </div>
            @endforelse
        </div>

    </div>
</div>
</div>
