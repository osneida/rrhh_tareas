<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Tarea;
use App\Models\User;
use App\Models\Cliente;

class TareaTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_example(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_can_create_tarea()
    {
        $user = User::factory()->create();
        $cliente = Cliente::factory()->create();

        $data = [
            'tarea' => 'Nueva tarea',
            'estatus' => 'Pendiente',
            'fecha' => now()->addDay()->toDateString(),
            'horas' => 2,
            'user_id' => $user->id,
            'cliente_id' => $cliente->id,
            'observacion' => 'Observación de prueba'
        ];

        $response = $this->post('/tareas', $data);

        $response->assertStatus(302); // Redirección después de crear
        $this->assertDatabaseHas('tareas', [
            'tarea' => 'Nueva tarea',
            'user_id' => $user->id,
            'cliente_id' => $cliente->id,
        ]);
    }

    public function test_can_update_tarea()
    {
        $user = User::factory()->create();
        $cliente = Cliente::factory()->create();
        $tarea = Tarea::factory()->create([
            'user_id' => $user->id,
            'cliente_id' => $cliente->id,
        ]);

        $updateData = [
            'tarea' => 'Tarea actualizada',
            'estatus' => 'Iniciada',
            'fecha' => now()->addDays(2)->toDateString(),
            'horas' => 3,
            'user_id' => $user->id,
            'cliente_id' => $cliente->id,
            'observacion' => 'Actualización'
        ];

        $response = $this->put("/tareas/{$tarea->id}", $updateData);

        $response->assertStatus(302);
        $this->assertDatabaseHas('tareas', [
            'id' => $tarea->id,
            'tarea' => 'Tarea actualizada',
            'estatus' => 'Iniciada',
        ]);
    }

    public function test_can_delete_tarea()
    {
        $user = User::factory()->create();
        $cliente = Cliente::factory()->create();
        $tarea = Tarea::factory()->create([
            'user_id' => $user->id,
            'cliente_id' => $cliente->id,
        ]);
        $response = $this->delete("/tareas/{$tarea->id}");

        $response->assertStatus(302);
        $this->assertDatabaseMissing('tareas', [
            'id' => $tarea->id,
        ]);
        }

        public function test_can_list_tareas()
        {
        $user = User::factory()->create();
        $cliente = Cliente::factory()->create();
        Tarea::factory()->count(3)->create([
            'user_id' => $user->id,
            'cliente_id' => $cliente->id,
        ]);

        $response = $this->get('/tareas');
        $response->assertStatus(200);
        $response->assertSee('tarea');
        }

        // Pruebas de clase de equivalencia para el campo 'tarea'
        public function test_tarea_min_length()
        {
        $user = User::factory()->create();
        $cliente = Cliente::factory()->create();

        // Menos de 3 caracteres (inválido)
        $data = [
            'tarea' => 'ab',
            'estatus' => 'Pendiente',
            'fecha' => now()->addDay()->toDateString(),
            'horas' => 2,
            'user_id' => $user->id,
            'cliente_id' => $cliente->id,
            'observacion' => 'Prueba min length'
        ];

        $response = $this->post('/tareas', $data);
        $response->assertSessionHasErrors('tarea');
        }

        public function test_tarea_exact_min_length()
        {
        $user = User::factory()->create();
        $cliente = Cliente::factory()->create();

        // Exactamente 3 caracteres (válido)
        $data = [
            'tarea' => 'abc',
            'estatus' => 'Pendiente',
            'fecha' => now()->addDay()->toDateString(),
            'horas' => 2,
            'user_id' => $user->id,
            'cliente_id' => $cliente->id,
            'observacion' => 'Prueba exact min length'
        ];

        $response = $this->post('/tareas', $data);
        $response->assertStatus(302);
        $this->assertDatabaseHas('tareas', [
            'tarea' => 'abc',
            'user_id' => $user->id,
            'cliente_id' => $cliente->id,
        ]);
        }

        public function test_tarea_max_length()
        {
        $user = User::factory()->create();
        $cliente = Cliente::factory()->create();

        // Exactamente 255 caracteres (válido)
        $tareaStr = str_repeat('a', 255);
        $data = [
            'tarea' => $tareaStr,
            'estatus' => 'Pendiente',
            'fecha' => now()->addDay()->toDateString(),
            'horas' => 2,
            'user_id' => $user->id,
            'cliente_id' => $cliente->id,
            'observacion' => 'Prueba max length'
        ];

        $response = $this->post('/tareas', $data);
        $response->assertStatus(302);
        $this->assertDatabaseHas('tareas', [
            'tarea' => $tareaStr,
            'user_id' => $user->id,
            'cliente_id' => $cliente->id,
        ]);
        }

        public function test_tarea_above_max_length()
        {
        $user = User::factory()->create();
        $cliente = Cliente::factory()->create();

        // Más de 255 caracteres (inválido)
        $tareaStr = str_repeat('a', 256);
        $data = [
            'tarea' => $tareaStr,
            'estatus' => 'Pendiente',
            'fecha' => now()->addDay()->toDateString(),
            'horas' => 2,
            'user_id' => $user->id,
            'cliente_id' => $cliente->id,
            'observacion' => 'Prueba above max length'
        ];

        $response = $this->post('/tareas', $data);
        $response->assertSessionHasErrors('tarea');
        }
    }
