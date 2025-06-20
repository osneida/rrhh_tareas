<?php

namespace Tests\Feature;

use App\Enums\EstatusTareaEnum;
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

    protected $cliente, $user;

    protected function setUp(): void
    {
        //para crearlo de forma global
        parent::setUp();
        $this->cliente = Cliente::factory()->create();
        $this->user = User::factory()->create();
    }

    public function test_status(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_can_create_tarea_with_livewire()
    {
        $this->actingAs($this->user);  //para que el usuario sea autenticado

        Livewire::test(TareaLivewire::class)
            ->set('tarea', 'Nueva tarea')
            ->set('estatus', EstatusTareaEnum::Pendiente)
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 2)
            ->set('user_id', $this->user->id)
            ->set('cliente_id', $this->cliente->id)
            ->set('observacion', 'Observación de prueba')
            ->call('store');

        $this->assertDatabaseHas('tareas', [
            'tarea' => 'Nueva tarea',
            'user_id' => $this->user->id,
            'cliente_id' => $this->cliente->id,
        ]);
    }

    public function test_tarea_min_length_livewire()
    {

        $this->actingAs($this->user);

        Livewire::test(TareaLivewire::class)
            ->set('tarea', 'ab')
            ->set('estatus', EstatusTareaEnum::Pendiente)
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 2)
            ->set('user_id', $this->user->id)
            ->set('cliente_id', $this->cliente->id)
            ->set('observacion', 'Prueba min length')
            ->call('store')
            ->assertHasErrors(['tarea' => 'min']);
    }

    public function test_tarea_exact_min_length_livewire()
    {

        $this->actingAs($this->user);

        Livewire::test(TareaLivewire::class)
            ->set('tarea', 'abc')
            ->set('estatus', EstatusTareaEnum::Pendiente)
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 2)
            ->set('user_id', $this->user->id)
            ->set('cliente_id', $this->cliente->id)
            ->set('observacion', 'Prueba exact min length')
            ->call('store');

        $this->assertDatabaseHas('tareas', [
            'tarea' => 'abc',
            'user_id' => $this->user->id,
            'cliente_id' => $this->cliente->id,
        ]);
    }

    public function test_tarea_max_length_livewire()
    {
        $this->actingAs($this->user);

        $tareaStr = str_repeat('a', 255);

        Livewire::test(TareaLivewire::class)
            ->set('tarea', $tareaStr)
            ->set('estatus', EstatusTareaEnum::Finalizada)
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 2)
            ->set('user_id', $this->user->id)
            ->set('cliente_id', $this->cliente->id)
            ->set('observacion', 'Prueba max length')
            ->call('store');

        $this->assertDatabaseHas('tareas', [
            'tarea' => $tareaStr,
            'user_id' => $this->user->id,
            'cliente_id' => $this->cliente->id,
        ]);
    }

    public function test_tarea_above_max_length_livewire()
    {
        $this->actingAs($this->user);

        $tareaStr = str_repeat('a', 256);

        Livewire::test(TareaLivewire::class)
            ->set('tarea', $tareaStr)
            ->set('estatus', EstatusTareaEnum::Iniciada)
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 2)
            ->set('user_id', $this->user->id)
            ->set('cliente_id', $this->cliente->id)
            ->set('observacion', 'Prueba above max length')
            ->call('store')
            ->assertHasErrors(['tarea' => 'max']);
    }

    public function test_delete_tarea()
    {
        $this->actingAs($this->user);

        $tareaComponent = Livewire::test(TareaLivewire::class)
            ->set('tarea', 'Tarea a eliminar')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 2)
            ->set('user_id', $this->user->id)
            ->set('cliente_id', $this->cliente->id)
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
        $this->actingAs($this->user);

        // Crear la tarea
        $tareaComponent = Livewire::test(TareaLivewire::class)
            ->set('tarea', 'Tarea protegida')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 2)
            ->set('user_id', $this->user->id)
            ->set('cliente_id', $this->cliente->id)
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
        $user    = User::factory()->count(3)->create(); // Crear 3 usuarios
        $userIds = $user->pluck('id')->toArray(); // Array de IDs
        $this->actingAs($this->user);

        Livewire::test(TareaLivewire::class)
            ->set('tarea', 'Tarea para varios usuarios')
            ->set('estatus', EstatusTareaEnum::Pendiente)
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 2)
            ->set('user_id',  $userIds)
            ->set('cliente_id', $this->cliente->id)
            ->set('observacion', 'varios empleados')
            ->call('store');

        // Verificar que se hayan creado tantas tareas como usuarios
        foreach ($user as $usuario) {
            $this->assertDatabaseHas('tareas', [
                'tarea' => 'Tarea para varios usuarios',
                'estatus' => EstatusTareaEnum::Pendiente,
                'fecha' => now()->addDay()->toDateString(),
                'horas' => 2,
                'cliente_id' => $this->cliente->id,
                'user_id' => $usuario->id,
            ]);
        }
    }

    public function test_crear_tarea_sin_usuarios()
    {
        $this->actingAs($this->user);

        Livewire::test(TareaLivewire::class)
            ->set('tarea', 'Tarea sin usuario o empleado')
            ->set('estatus', EstatusTareaEnum::Pendiente)
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 2)
            ->set('cliente_id', $this->cliente->id)
            ->set('observacion', 'varios empleados')
            ->call('store');

        // Verificar que se hayan creado tantas tareas como usuarios
        $this->assertDatabaseHas('tareas', [
            'tarea' => 'Tarea sin usuario o empleado',
            'cliente_id' => $this->cliente->id,
        ]);
    }

    public function test_no_crear_tarea_con_fecha_pasada()
    {
        $this->actingAs($this->user);

        Livewire::test(TareaLivewire::class)
            ->set('tarea', 'Fecha en el pasado')
            ->set('fecha', now()->subDay()) // Fecha en el pasado
            ->set('horas', 2)
            ->set('cliente_id', $this->cliente->id)
            ->call('store')
            ->assertHasErrors(['fecha' => 'after_or_equal:today']);
    }

    public function test_update_tarea_con_fecha_pasada()
    {
        $this->actingAs($this->user);

        // Crear la tarea
        $tareaComponent = Livewire::test(TareaLivewire::class)
            ->set('tarea', 'Update Tarea cualquier fecha')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 2)
            ->set('estatus', EstatusTareaEnum::Pendiente)
            ->set('cliente_id', $this->cliente->id)
            ->call('store');

        $tareaId = $tareaComponent->get('tareaTest')->id;

        $fechaPasada = now()->subDay()->toDateString();

        Livewire::test(TareaLivewire::class, ['tarea_id' => $tareaId])
            ->set('tarea', 'Update Tarea cualquier fecha')
            ->set('fecha', $fechaPasada) // Fecha en el pasado
            ->set('horas', 2)
            ->set('estatus', EstatusTareaEnum::Pendiente)
            ->set('cliente_id', $this->cliente->id)
            ->call('update');

        // Aserción: la fecha se actualizó correctamente
        $this->assertDatabaseHas('tareas', [
            'id' => $tareaId,
            'fecha' => $fechaPasada,
        ]);
    }

    public function test_no_crear_tarea_con_hora_fuera_rango_max()
    {
        $this->actingAs($this->user);

        Livewire::test(TareaLivewire::class)
            ->set('tarea', 'Hora permitida 1 a 10')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 12)
            ->set('cliente_id', $this->cliente->id)
            ->call('store')
            ->assertHasErrors(['horas' => 'max']);
    }

    public function test_no_crear_tarea_con_hora_fuera_rango_min()
    {
        $this->actingAs($this->user);

        Livewire::test(TareaLivewire::class)
            ->set('tarea', 'Hora permitida 1 a 10')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 0)
            ->set('cliente_id', $this->cliente->id)
            ->call('store')
            ->assertHasErrors(['horas' => 'min']);
    }

    public function test_no_crear_tarea_cliente_no_existe()
    {
        $this->actingAs($this->user);

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
        $this->actingAs($this->user);

        Livewire::test(TareaLivewire::class)
            ->set('tarea', 'Cliente no existe')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 1)
            ->set('user_id', 120000000)
            ->set('cliente_id', $this->cliente->id)
            ->call('store')
            ->assertHasErrors(['user_id' => 'exists:users,id']);
    }

    public function test_no_crear_tarea_requerida()
    {
        $this->actingAs($this->user);

        Livewire::test(TareaLivewire::class)
            ->set('tarea', '')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 1)
            ->set('cliente_id', $this->cliente->id)
            ->call('store')
            ->assertHasErrors(['tarea' => 'required']);
    }

    public function test_no_crear_fecha_requerida()
    {
        $this->actingAs($this->user);

        Livewire::test(TareaLivewire::class)
            ->set('tarea', 'La fecha es requerida')
            ->set('horas', 1)
            ->set('cliente_id', $this->cliente->id)
            ->call('store')
            ->assertHasErrors(['fecha' => 'required']);
    }

    public function test_no_crear_horas_requeridas()
    {
        $this->actingAs($this->user);

        Livewire::test(TareaLivewire::class)
            ->set('tarea', 'La hora es requerida')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', '')
            ->set('cliente_id', $this->cliente->id)
            ->call('store')
            ->assertHasErrors(['horas' => 'required']);
    }

    public function test_no_crear_cliente_requerido()
    {
        $this->actingAs($this->user);

        Livewire::test(TareaLivewire::class)
            ->set('tarea', 'La hora es requerida')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 1)
            ->call('store')
            ->assertHasErrors(['cliente_id' => 'required']);
    }

    public function test_only_admin_can_crear_tarea()
    {
        $userEmpleado = User::factory()->create(['role' => 'empleado']);

        // User cannot create
        $this->actingAs($userEmpleado);
        Livewire::test(TareaLivewire::class)
            ->set('tarea', 'user no admin')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 2)
            ->set('estatus', EstatusTareaEnum::Pendiente)
            ->set('cliente_id', $this->cliente->id)
            ->call('store')
            ->assertForbidden();
    }

    public function test_only_admin_can_update_tarea()
    {
        $userEmpleado = User::factory()->create(['role' => 'empleado']);

        // Admin can update
        $this->actingAs($this->user);
        $tareaComponent = Livewire::test(TareaLivewire::class)
            ->set('tarea', 'user admin')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 2)
            ->set('estatus', EstatusTareaEnum::Pendiente)
            ->set('cliente_id', $this->cliente->id)
            ->call('store');

        $tareaId = $tareaComponent->get('tareaTest')->id;

        $this->assertDatabaseHas('tareas', [
            'id' => $tareaId
        ]);


        Livewire::test(TareaLivewire::class, ['tarea_id' => $tareaId])
            ->set('tarea', 'Update Tarea cualquier')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 2)
            ->set('estatus', EstatusTareaEnum::Pendiente)
            ->set('cliente_id', $this->cliente->id)
            ->call('update');

        $this->assertDatabaseHas('tareas', [
            'id'    => $tareaId,
            'tarea' => 'Update Tarea cualquier',
        ]);

        // userEmpleado cannot update
        $this->actingAs($userEmpleado);
        Livewire::test(TareaLivewire::class, ['tarea_id' => $tareaId])
            ->set('tarea', 'Update Tarea cualquier')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 2)
            ->set('estatus', EstatusTareaEnum::Pendiente)
            ->set('cliente_id', $this->cliente->id)
            ->call('update')
            ->assertForbidden();
    }

    public function test_only_admin_can_delete_tarea()
    {
        $userEmpleado = User::factory()->create(['role' => 'user']);

        // Admin can delete if no tareas
        $this->actingAs($this->user);
        $tareaComponent = Livewire::test(TareaLivewire::class)
            ->set('tarea', 'user admin')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 2)
            ->set('estatus', EstatusTareaEnum::Pendiente)
            ->set('cliente_id', $this->cliente->id)
            ->call('store');

        $tareaId = $tareaComponent->get('tareaTest')->id;

        $this->assertDatabaseHas('tareas', [
            'id' => $tareaId
        ]);

        Livewire::test(TareaLivewire::class, ['tarea_id' => $tareaId])
            ->call('destroy');

        $this->assertDatabaseMissing('tareas', ['id' => $tareaId]);

        // User empleado cannot delete
        $tareaComponent = Livewire::test(TareaLivewire::class)
            ->set('tarea', 'user empleado')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 2)
            ->set('estatus', EstatusTareaEnum::Pendiente)
            ->set('cliente_id', $this->cliente->id)
            ->call('store');

        $tareaId = $tareaComponent->get('tareaTest')->id;

        $this->assertDatabaseHas('tareas', [
            'id' => $tareaId
        ]);

        $this->actingAs($userEmpleado);
        Livewire::test(TareaLivewire::class, ['tarea_id' => $tareaId])
            ->call('destroy')
            ->assertForbidden();
    }
}
