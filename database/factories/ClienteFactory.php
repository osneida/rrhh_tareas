<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
class ClienteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'mail' => fake()->unique()->safeEmail(),
        ];
    }
}
