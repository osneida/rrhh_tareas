<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tarea>
 */
class TareaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tarea' => 'Nueva tarea',
            'estatus' => 'Pendiente',
            'fecha' => now()->addDay()->toDateString(),
            'horas' => 2,
            'user_id' => 1,
            'cliente_id' => 1,
            'observacion' => 'Observación de prueba factory'
        ];
    }
}
