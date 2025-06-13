<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl mb-2">
        @if ($isAdmin)
            <div class="grid auto-rows-min gap-4 md:grid-cols-4">

                <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200">
                    <div class="bg-cyan-700 rounded-xl shadow p-4 flex flex-col justify-between h-full dark:bg-zinc-900">


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
                    <div
                        class="bg-lime-700 rounded-xl shadow p-4 flex flex-col justify-between h-full dark:bg-zinc-900">


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
                    <div
                        class="bg-orange-400 rounded-xl shadow p-4 flex flex-col justify-between h-full dark:bg-zinc-900">


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
                    <div
                        class="bg-teal-700 rounded-xl shadow p-4 flex flex-col justify-between h-full dark:bg-zinc-900">


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
    </div>

    <div class="w-full max-w-80 p-6 bg-white dark:bg-zinc-900 mb-4 shadow">
        <div class="grid auto-rows-min gap-4 md:grid-cols-4">
            <div class="col-span-2 col-start-1 font-extrabold">{{__('Working day')}}</div>
            <div class="col-span-2 col-start-3 font-extrabold">{{ $hoy }}</div>
        </div>

        @forelse ($tarea_hoy as $tarea)
            <div class="stats stats-vertical shadow mt-4 dark:text-zinc-900">
                <div class="stat">
                    <div class="stat-title  bg-blue-100">
                        <p class="text-justify"><b>{{ __('Task') }} </b>:
                            {{ $tarea->tarea }} </p>

                        @if ($tarea->observacion)
                            <p><b>{{ __('Observation') }} </b>:
                                {{ $tarea->observacion }} </p>
                        @endif

                        <p class="mt-2"><b>{{ __('Client') }} </b>:
                            {{ $tarea->cliente->name . ' | ' . $tarea->cliente->address }}</p>
                    </div>
                    <div class="stat-value mt-2  w-1/3 flex gap-2">
                        <button type="button" wire:click="guardar_hora_inicio({{ $tarea->id }})"
                            :key="hora_inicio - {{ $tarea->id }}"
                            {{ isset($enables_inicio[$tarea->id]) ? $enables_inicio[$tarea->id] : '' }}
                            class=" p-1 bg-blue-400 text-white dark:text-zinc-900 rounded disabled:bg-gray-400 hover:bg-blue-500 transition">{{ __('Inicio Actividad') }}</button>
                        <button type="button" wire:click="guardar_hora_fin({{ $tarea->id }})"
                            :key="hora_fin - {{ $tarea->id }}"
                            {{ isset($enables_fin[$tarea->id]) ? $enables_fin[$tarea->id] : '' }}
                            class="p-1 bg-red-500 text-white dark:text-zinc-900 rounded disabled:bg-gray-400 hover:bg-red-700 transition">{{ __('Fin Actividad') }}
                        </button>
                    </div>
                    <div class="stat-desc">


                        <input type="text" class="input input-bordered input-sm w-24 "
                            value="{{ isset($horas_inicio[$tarea->id]) ? $horas_inicio[$tarea->id] : '' }}" readonly
                            id="hora_inicio_text-{{ $tarea->id }}" />

                        <input type="text" class="input input-bordered input-sm w-24 "
                            value="{{ isset($horas_fin[$tarea->id]) ? $horas_fin[$tarea->id] : '' }}" readonly
                            id="hora_fin_text-{{ $tarea->id }}" />

                    </div>
                </div>
            </div>



        @empty

            <div class="stats stats-vertical shadow mt-4 dark:text-zinc-900">
                <div class="stat">
                     <div class="stat-title  bg-blue-100">
                        <p class="text-justify"><b>{{ __('No tasks for today') }}  </b>                            </p>

                    </div>
                    <div class="stat-value mt-2">

                    </div>
                    <div class="stat-desc">




                    </div>
                </div>
            </div>
        @endforelse
    </div>

</div>

</div>
