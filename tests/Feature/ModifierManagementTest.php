<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Staff\ModifierManager;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ModifierManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_modifier_manager(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);

        $response = $this->actingAs($admin)->get('/staff/modifiers');

        $response->assertStatus(200);
        $response->assertSeeLivewire(ModifierManager::class);
    }

    public function test_bar_cannot_access_modifier_manager(): void
    {
        $bar = User::factory()->create(['role' => UserRole::Bar, 'is_active' => true]);

        $response = $this->actingAs($bar)->get('/staff/modifiers');

        $response->assertStatus(403);
    }

    public function test_admin_can_create_modifier_group_and_option(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);

        Livewire::actingAs($admin)
            ->test(ModifierManager::class)
            ->call('openCreateGroupModal')
            ->set('groupName', 'Suhu Minuman')
            ->set('isRequired', true)
            ->set('minSelect', 1)
            ->set('maxSelect', 1)
            ->call('saveGroup')
            ->assertHasNoErrors();

        $group = ModifierGroup::where('name', 'Suhu Minuman')->first();
        $this->assertNotNull($group);
        $this->assertTrue($group->is_required);

        Livewire::actingAs($admin)
            ->test(ModifierManager::class)
            ->call('openCreateOptionModal', $group->id)
            ->set('optionName', 'Iced / Dingin')
            ->set('priceDelta', 2000)
            ->set('optionIsAvailable', true)
            ->set('optionSortOrder', 1)
            ->call('saveOption')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('modifier_options', [
            'modifier_group_id' => $group->id,
            'name' => 'Iced / Dingin',
            'price_delta' => 2000,
            'is_available' => true,
        ]);
    }

    public function test_admin_can_toggle_option_availability(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
        $option = ModifierOption::factory()->create(['is_available' => true]);

        Livewire::actingAs($admin)
            ->test(ModifierManager::class)
            ->call('toggleOptionAvailability', $option->id);

        $this->assertFalse($option->fresh()->is_available);
    }

    public function test_admin_can_delete_modifier_option(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
        $option = ModifierOption::factory()->create();

        Livewire::actingAs($admin)
            ->test(ModifierManager::class)
            ->call('deleteOption', $option->id);

        $this->assertDatabaseMissing('modifier_options', ['id' => $option->id]);
    }
}
