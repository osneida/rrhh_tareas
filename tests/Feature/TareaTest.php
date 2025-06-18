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

    public function test_delete_tarea()
    {
        $user = User::factory()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($user);

        $tareaComponent = Livewire::test(TareaLivewire::class)
            ->set('tarea', 'Tarea a eliminar')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 2)
            ->set('user_id', $user->id)
            ->set('cliente_id', $cliente->id)
            ->set('observacion', 'Observación para eliminar')
            ->call('store');

        $tareaId = $tareaComponent->get('tareaTest')->id;

        $this->assertDatabaseHas('tareas', [
            'id' => $tareaId,
            'tarea' => 'Tarea a eliminar',
        ]);

        // Eliminar la tarea
        $tarea = Tarea::findOrFail($tareaId);
        $tarea->delete();

        $this->assertDatabaseMissing('tareas', [
            'id' => $tarea->id,
        ]);
    }

    public function test_no_se_puede_borrar_tarea_con_jornada_laboral()
    {
        $user = User::factory()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($user);

        // Crear la tarea
        $tareaComponent = Livewire::test(TareaLivewire::class)
            ->set('tarea', 'Tarea protegida')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 2)
            ->set('user_id', $user->id)
            ->set('cliente_id', $cliente->id)
            ->set('observacion', 'No debe poder eliminarse')
            ->call('store');

        $tareaId = $tareaComponent->get('tareaTest')->id;

        \App\Models\JornadaLaboral::updateOrCreate(
            ['tarea_id' => $tareaId],
            [
                'fecha'        => date("y/m/d"),
                'hora_inicio'  => date("H:i:s"),
            ]
        );

        // Intentar eliminar la tarea
        try {
            $tarea = Tarea::findOrFail($tareaId);
            $tarea->delete();
        } catch (\Exception $e) {
            // Si lanza excepción, la prueba sigue
        }

        // Verificar que la tarea sigue en la base de datos
        $this->assertDatabaseHas('tareas', [
            'id' => $tarea->id,
            'tarea' => 'Tarea protegida',
        ]);
    }

    public function test_crear_tarea_para_varios_usuarios()
    {
        $cliente = Cliente::factory()->create();
        $user    = User::factory()->count(3)->create(); // Crear 3 usuarios
        $userIds = $user->pluck('id')->toArray(); // Array de IDs

        Livewire::test(TareaLivewire::class)
            ->set('tarea', 'Tarea para varios usuarios')
            ->set('estatus', 'Pendiente')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 2)
            ->set('user_id', $userIds)
            ->set('cliente_id', $cliente->id)
            ->set('observacion', 'varios empleados')
            ->call('store');

        // Verificar que se hayan creado tantas tareas como usuarios
        foreach ($user as $usuario) {
            $this->assertDatabaseHas('tareas', [
                'tarea' => 'Tarea para varios usuarios',
                'estatus' => 'Pendiente',
                'fecha' => now()->addDay()->toDateString(),
                'horas' => 2,
                'cliente_id' => $cliente->id,
                'user_id' => $usuario->id,
            ]);
        }
    }

    public function test_crear_tarea_sin_usuarios()
    {
        $cliente = Cliente::factory()->create();

        Livewire::test(TareaLivewire::class)
            ->set('tarea', 'Tarea sin usuario o empleado')
            ->set('estatus', 'Pendiente')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 2)
            ->set('cliente_id', $cliente->id)
            ->set('observacion', 'varios empleados')
            ->call('store');

        // Verificar que se hayan creado tantas tareas como usuarios
        $this->assertDatabaseHas('tareas', [
            'tarea' => 'Tarea sin usuario o empleado',
            'cliente_id' => $cliente->id,
        ]);
    }

    public function test_no_crear_tarea_con_fecha_pasada()
    {
        $cliente = Cliente::factory()->create();

        Livewire::test(TareaLivewire::class)
            ->set('tarea', 'Fecha en el pasado')
            ->set('fecha', now()->subDay()) // Fecha en el pasado
            ->set('horas', 2)
            ->set('cliente_id', $cliente->id)
            ->call('store')
            ->assertHasErrors(['fecha' => 'after_or_equal:today']);
    }

    public function test_no_crear_tarea_con_hora_fuera_rango_max()
    {
        $cliente = Cliente::factory()->create();

        Livewire::test(TareaLivewire::class)
            ->set('tarea', 'Hora permitida 1 a 10')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 12)
            ->set('cliente_id', $cliente->id)
            ->call('store')
            ->assertHasErrors(['horas' => 'max']);
    }

    public function test_no_crear_tarea_con_hora_fuera_rango_min()
    {
        $cliente = Cliente::factory()->create();

        Livewire::test(TareaLivewire::class)
            ->set('tarea', 'Hora permitida 1 a 10')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 0)
            ->set('cliente_id', $cliente->id)
            ->call('store')
            ->assertHasErrors(['horas' => 'min']);
    }

    public function test_no_crear_tarea_cliente_no_existe()
    {
        Livewire::test(TareaLivewire::class)
            ->set('tarea', 'Cliente no existe')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 1)
            ->set('cliente_id', 120000000)
            ->call('store')
            ->assertHasErrors(['cliente_id' => 'exists:clientes,id']);
    }

    public function test_no_crear_tarea_user_no_existe()
    {
        $cliente = Cliente::factory()->create();

        Livewire::test(TareaLivewire::class)
            ->set('tarea', 'Cliente no existe')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 1)
            ->set('user_id', 120000000)
            ->set('cliente_id', $cliente->id)
            ->call('store')
            ->assertHasErrors(['user_id' => 'exists:users,id']);
    }
}
