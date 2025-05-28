<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Tarea;
use App\Models\User;
use App\Models\Cliente;
use Livewire\Livewire;
use App\Livewire\Admin\TareaLivewire;

class TareaTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_status(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_can_create_tarea_with_livewire()
    {
        $user = User::factory()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($user);

        Livewire::test(TareaLivewire::class)
            ->set('tarea', 'Nueva tarea')
            ->set('estatus', 'Pendiente')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 2)
            ->set('user_id', $user->id)
            ->set('cliente_id', $cliente->id)
            ->set('observacion', 'Observación de prueba')
            ->call('store');

        $this->assertDatabaseHas('tareas', [
            'tarea' => 'Nueva tarea',
            'user_id' => $user->id,
            'cliente_id' => $cliente->id,
        ]);
    }

    public function test_tarea_min_length_livewire()
    {
        $user = User::factory()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($user);

        Livewire::test(TareaLivewire::class)
            ->set('tarea', 'ab')
            ->set('estatus', 'Pendiente')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 2)
            ->set('user_id', $user->id)
            ->set('cliente_id', $cliente->id)
            ->set('observacion', 'Prueba min length')
            ->call('store')
            ->assertHasErrors(['tarea' => 'min']);
    }

    public function test_tarea_exact_min_length_livewire()
    {
        $user = User::factory()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($user);

        Livewire::test(TareaLivewire::class)
            ->set('tarea', 'abc')
            ->set('estatus', 'Pendiente')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 2)
            ->set('user_id', $user->id)
            ->set('cliente_id', $cliente->id)
            ->set('observacion', 'Prueba exact min length')
            ->call('store');

        $this->assertDatabaseHas('tareas', [
            'tarea' => 'abc',
            'user_id' => $user->id,
            'cliente_id' => $cliente->id,
        ]);
    }

    public function test_tarea_max_length_livewire()
    {
        $user = User::factory()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($user);

        $tareaStr = str_repeat('a', 255);

        Livewire::test(TareaLivewire::class)
            ->set('tarea', $tareaStr)
            ->set('estatus', 'Pendiente')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 2)
            ->set('user_id', $user->id)
            ->set('cliente_id', $cliente->id)
            ->set('observacion', 'Prueba max length')
            ->call('store');

        $this->assertDatabaseHas('tareas', [
            'tarea' => $tareaStr,
            'user_id' => $user->id,
            'cliente_id' => $cliente->id,
        ]);
    }

    public function test_tarea_above_max_length_livewire()
    {
        $user = User::factory()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($user);

        $tareaStr = str_repeat('a', 256);

        Livewire::test(TareaLivewire::class)
            ->set('tarea', $tareaStr)
            ->set('estatus', 'Pendiente')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 2)
            ->set('user_id', $user->id)
            ->set('cliente_id', $cliente->id)
            ->set('observacion', 'Prueba above max length')
            ->call('store')
            ->assertHasErrors(['tarea' => 'max']);
    }
}
