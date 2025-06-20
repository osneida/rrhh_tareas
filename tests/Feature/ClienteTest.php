<?php

namespace Tests\Feature;

use App\Enums\StatusEnum;
use App\Livewire\Admin\ClienteLivewire;
use App\Livewire\Admin\TareaLivewire;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Livewire\Livewire;

class ClienteTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $userAdmin;
    protected function setUp(): void
    {
        //para crearlo de forma global
        parent::setUp();
        $this->userAdmin = User::factory()->create(['role' => 'admin']);
    }

    public function test_status(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_can_create_cliente_with_livewire()
    {
        $this->actingAs($this->userAdmin);  //para que el usuario sea autenticado

        Livewire::test(ClienteLivewire::class)
            ->set('name', 'Cliente Test')
            ->set('address', 'Su dirección test')
            ->set('mail', 'clientetest@prueba.com')
            ->set('phone', 'tel 123456')
            ->set('cif', 'cif_test')
            ->call('store');

        $this->assertDatabaseHas('clientes', [
            'name' => 'Cliente Test',
            'cif'  => 'cif_test'
        ]);
    }

    public function test_can_create_cliente_required_with_livewire()
    {
        $this->actingAs($this->userAdmin);

        Livewire::test(ClienteLivewire::class)
            ->set('name', 'Cliente Test')
            ->call('store');

        $this->assertDatabaseHas('clientes', [
            'name' => 'Cliente Test',
        ]);
    }

    public function test_name_is_required()
    {
        $this->actingAs($this->userAdmin);

        Livewire::test(ClienteLivewire::class)
            ->set('name', '')
            ->call('store')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_name_within_length_limits()
    {
        $this->actingAs($this->userAdmin);
        Livewire::test(ClienteLivewire::class)
            ->set('name', str_repeat('a', 46))
            ->call('store')
            ->assertHasErrors(['name' => 'max']);
    }

    public function test_name_within_length_min()
    {
        $this->actingAs($this->userAdmin);
        Livewire::test(ClienteLivewire::class)
            ->set('name', 'ab')
            ->call('store')
            ->assertHasErrors(['name' => 'min']);
    }


    public function test_unique_fields_cif_mail_phone()
    {
        $this->actingAs($this->userAdmin);

        $data = [
            'name' => 'Cliente2',
            'cif' => 'CIF1',
            'mail' => 'mail1@test.com',
            'phone' => '123456',
            'status' => StatusEnum::ACTIVE
        ];

        Livewire::test(ClienteLivewire::class)
            ->set('name', $data['name'])
            ->set('cif', $data['cif'])
            ->set('mail', $data['mail'])
            ->set('phone', $data['phone'])
            ->set('status', $data['status'])
            ->call('store');

        // Try to create another with same unique fields
        foreach (['cif', 'mail', 'phone'] as $field) {
            $testData = [
                'name' => 'Cliente2',
                'cif'     => $field === 'cif'     ? $data['cif'] : null,
                'mail'    => $field === 'mail'    ? $data['mail'] : null,
                'phone'   => $field === 'phone'   ? $data['phone'] : null
            ];

            Livewire::test(ClienteLivewire::class)
                ->set('name', $testData['name'])
                ->set('cif', $testData['cif'])
                ->set('mail', $testData['mail'])
                ->set('phone', $testData['phone'])
                ->call('store')
                ->assertHasErrors([$field => 'unique']);
        }
    }

    public function test_status_invalid()
    {
        $this->actingAs($this->userAdmin);

        // Status invalid
        Livewire::test(ClienteLivewire::class)
            ->set('name', 'Cliente Test')
            ->set('status', 18)
            ->call('store')
            ->assertHasErrors(['status']);
    }

    public function test_status_enum()
    {
        $this->actingAs($this->userAdmin);
        $selectStatus = StatusEnum::cases();
        foreach ($selectStatus as $status) {
            Livewire::test(ClienteLivewire::class)
                ->set('name', 'Cliente Test')
                ->set('status', $status)
                ->call('store')
                ->assertHasNoErrors(['status']);
        }
    }

    public function test_only_admin_can_crear_clientes()
    {
        $user = User::factory()->create(['role' => 'user']);

        // User cannot create
        $this->actingAs($user);
        Livewire::test(ClienteLivewire::class)
            ->set('name', 'User Cliente')
            ->set('status', StatusEnum::ACTIVE)
            ->call('store')
            ->assertForbidden();
    }

    public function test_only_admin_can_update_clientes()
    {
        $user = User::factory()->create(['role' => 'user']);

        // Admin can update
        $cliente = \App\Models\Cliente::factory()->create();

        $this->actingAs($this->userAdmin);
        Livewire::test(ClienteLivewire::class, ['cliente_id' => $cliente->id])
            ->set('name', 'Updated Cliente')
            ->call('update');
        $this->assertDatabaseHas('clientes', ['name' => 'Updated Cliente']);

        // User cannot update
        $this->actingAs($user);
        Livewire::test(ClienteLivewire::class, ['cliente_id' => $cliente->id])
            ->set('name', 'User Update')
            ->call('update')
            ->assertForbidden();
    }

    public function test_only_admin_can_delete_clientes()
    {
        $user = User::factory()->create(['role' => 'user']);

        // Admin can delete if no tareas
        $clienteSinTareas = \App\Models\Cliente::factory()->create();
        $this->actingAs($this->userAdmin);
        Livewire::test(ClienteLivewire::class, ['cliente_id' => $clienteSinTareas->id])
            ->call('destroy');
        $this->assertDatabaseMissing('clientes', ['id' => $clienteSinTareas->id]);

        // User cannot delete
        $cliente2 = \App\Models\Cliente::factory()->create();
        $this->actingAs($user);
        Livewire::test(ClienteLivewire::class, ['cliente_id' => $cliente2->id])
            ->call('destroy')
            ->assertForbidden();
    }

    public function test_cannot_delete_cliente_with_tareas()
    {
        $cliente = \App\Models\Cliente::factory()->create(['status' => StatusEnum::ACTIVE]);
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($this->userAdmin);

        Livewire::test(TareaLivewire::class)
            ->set('tarea', 'Tarea protegida')
            ->set('fecha', now()->addDay()->toDateString())
            ->set('horas', 2)
            ->set('user_id', $user->id)
            ->set('cliente_id', $cliente->id)
            ->set('observacion', 'No debe poder eliminarse')
            ->call('store');

        $this->assertDatabaseHas('tareas', [
            'tarea' => 'Tarea protegida',
            'cliente_id' => $cliente->id,
        ]);

        // Intentar eliminar el cliente
        try {
            Livewire::test(ClienteLivewire::class, ['cliente_id' => $cliente->id])
                ->call('destroy');
        } catch (\Exception $e) {
            // Si lanza excepción, la prueba sigue
        }

        $this->assertDatabaseHas('clientes', [
            'id' => $cliente->id
        ]);
    }

    public function test_can_delete_cliente_sin_tareas()
    {
        $this->actingAs($this->userAdmin);
        $cliente = \App\Models\Cliente::factory()->create();

        Livewire::test(ClienteLivewire::class, ['cliente_id' => $cliente->id])
            ->call('destroy');

        $this->assertDatabaseMissing('clientes', [
            'id' => $cliente->id
        ]);
    }
}
