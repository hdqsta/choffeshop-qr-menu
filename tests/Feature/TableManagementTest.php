<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Staff\TableManager;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TableManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_table_manager(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);

        $response = $this->actingAs($admin)->get('/staff/tables');

        $response->assertStatus(200);
        $response->assertSeeLivewire(TableManager::class);
    }

    public function test_cashier_cannot_access_table_manager(): void
    {
        $cashier = User::factory()->create(['role' => UserRole::Cashier, 'is_active' => true]);

        $response = $this->actingAs($cashier)->get('/staff/tables');

        $response->assertStatus(403);
    }

    public function test_admin_can_create_table(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);

        Livewire::actingAs($admin)
            ->test(TableManager::class)
            ->call('openCreateTableModal')
            ->set('number', '99')
            ->set('isActive', true)
            ->call('saveTable')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tables', [
            'number' => '99',
            'is_active' => true,
        ]);

        $table = Table::where('number', '99')->first();
        $this->assertNotEmpty($table->token);
    }

    public function test_admin_can_toggle_table_status(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
        $table = Table::factory()->create(['is_active' => true]);

        Livewire::actingAs($admin)
            ->test(TableManager::class)
            ->call('toggleTableStatus', $table->id);

        $this->assertFalse($table->fresh()->is_active);
    }

    public function test_admin_can_regenerate_table_token(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
        $table = Table::factory()->create(['token' => 'old-token']);

        Livewire::actingAs($admin)
            ->test(TableManager::class)
            ->call('regenerateToken', $table->id);

        $this->assertNotSame('old-token', $table->fresh()->token);
    }

    public function test_admin_can_delete_table_without_orders(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
        $table = Table::factory()->create();

        Livewire::actingAs($admin)
            ->test(TableManager::class)
            ->call('deleteTable', $table->id);

        $this->assertDatabaseMissing('tables', ['id' => $table->id]);
    }
}
