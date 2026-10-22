<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Staff\StaffManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
    }

    public function test_admin_can_view_staff_management_page(): void
    {
        $response = $this->actingAs($this->admin())->get('/staff/users');

        $response->assertStatus(200);
        $response->assertSeeLivewire(StaffManager::class);
        $response->assertSee('Manajemen Akun Staf');
    }

    public function test_cashier_and_barista_cannot_access_staff_management_page(): void
    {
        $cashier = User::factory()->create(['role' => UserRole::Cashier, 'is_active' => true]);
        $barista = User::factory()->create(['role' => UserRole::Bar, 'is_active' => true]);

        $this->actingAs($cashier)->get('/staff/users')->assertForbidden();
        $this->actingAs($barista)->get('/staff/users')->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/staff/users')->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_use_the_component_actions_directly(): void
    {
        $cashier = User::factory()->create(['role' => UserRole::Cashier, 'is_active' => true]);

        Livewire::actingAs($cashier)
            ->test(StaffManager::class)
            ->assertForbidden();
    }

    public function test_admin_can_create_a_staff_account(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(StaffManager::class)
            ->call('openCreateStaffModal')
            ->set('name', 'Sari Kasir')
            ->set('email', 'sari@coffeeshop.test')
            ->set('role', UserRole::Cashier->value)
            ->set('password', 'rahasia123')
            ->set('password_confirmation', 'rahasia123')
            ->set('is_active', true)
            ->call('saveStaff')
            ->assertHasNoErrors();

        $staff = User::where('email', 'sari@coffeeshop.test')->first();

        $this->assertNotNull($staff);
        $this->assertSame('Sari Kasir', $staff->name);
        $this->assertSame(UserRole::Cashier, $staff->role);
        $this->assertTrue($staff->is_active);
        $this->assertTrue(Hash::check('rahasia123', $staff->password));
        $this->assertNotNull($staff->email_verified_at);
    }

    public function test_created_staff_can_authenticate_and_reach_the_dashboard(): void
    {
        Livewire::actingAs($this->admin())
            ->test(StaffManager::class)
            ->call('openCreateStaffModal')
            ->set('name', 'Budi Barista')
            ->set('email', 'budi@coffeeshop.test')
            ->set('role', UserRole::Bar->value)
            ->set('password', 'rahasia123')
            ->set('password_confirmation', 'rahasia123')
            ->call('saveStaff')
            ->assertHasNoErrors();

        $staff = User::where('email', 'budi@coffeeshop.test')->firstOrFail();

        $this->actingAs($staff)->get('/dashboard')->assertOk();
    }

    public function test_staff_creation_validates_email_uniqueness_and_password_confirmation(): void
    {
        User::factory()->create(['email' => 'kasir@coffeeshop.test']);

        Livewire::actingAs($this->admin())
            ->test(StaffManager::class)
            ->call('openCreateStaffModal')
            ->set('name', 'Duplikat')
            ->set('email', 'kasir@coffeeshop.test')
            ->set('role', UserRole::Cashier->value)
            ->set('password', 'rahasia123')
            ->set('password_confirmation', 'berbeda123')
            ->call('saveStaff')
            ->assertHasErrors(['email', 'password']);

        $this->assertSame(1, User::where('email', 'kasir@coffeeshop.test')->count());
    }

    public function test_staff_creation_requires_a_role(): void
    {
        Livewire::actingAs($this->admin())
            ->test(StaffManager::class)
            ->call('openCreateStaffModal')
            ->set('name', 'Tanpa Peran')
            ->set('email', 'tanpaperan@coffeeshop.test')
            ->set('role', '')
            ->set('password', 'rahasia123')
            ->set('password_confirmation', 'rahasia123')
            ->call('saveStaff')
            ->assertHasErrors(['role']);

        $this->assertDatabaseMissing('users', ['email' => 'tanpaperan@coffeeshop.test']);
    }

    public function test_admin_can_update_a_staff_account_and_reset_its_password(): void
    {
        $admin = $this->admin();
        $staff = User::factory()->create([
            'name' => 'Kasir Lama',
            'email' => 'lama@coffeeshop.test',
            'role' => UserRole::Cashier,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(StaffManager::class)
            ->call('openEditStaffModal', $staff->id)
            ->assertSet('name', 'Kasir Lama')
            ->set('name', 'Kasir Baru')
            ->set('role', UserRole::Bar->value)
            ->set('password', 'sandiBaru123')
            ->set('password_confirmation', 'sandiBaru123')
            ->call('saveStaff')
            ->assertHasNoErrors();

        $staff->refresh();

        $this->assertSame('Kasir Baru', $staff->name);
        $this->assertSame(UserRole::Bar, $staff->role);
        $this->assertTrue(Hash::check('sandiBaru123', $staff->password));
    }

    public function test_password_is_untouched_when_left_empty_on_edit(): void
    {
        $admin = $this->admin();
        $staff = User::factory()->create([
            'role' => UserRole::Cashier,
            'is_active' => true,
            'password' => 'sandiAwal123',
        ]);

        $originalHash = $staff->password;

        Livewire::actingAs($admin)
            ->test(StaffManager::class)
            ->call('openEditStaffModal', $staff->id)
            ->set('name', 'Nama Diperbarui')
            ->call('saveStaff')
            ->assertHasNoErrors();

        $this->assertSame($originalHash, $staff->fresh()->password);
    }

    public function test_admin_can_deactivate_and_activate_another_staff_account(): void
    {
        $admin = $this->admin();
        $staff = User::factory()->create(['role' => UserRole::Cashier, 'is_active' => true]);

        Livewire::actingAs($admin)
            ->test(StaffManager::class)
            ->call('toggleStaffStatus', $staff->id);

        $this->assertFalse($staff->fresh()->is_active);

        Livewire::actingAs($admin)
            ->test(StaffManager::class)
            ->call('toggleStaffStatus', $staff->id);

        $this->assertTrue($staff->fresh()->is_active);
    }

    public function test_admin_cannot_delete_or_deactivate_their_own_account(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(StaffManager::class)
            ->call('deleteStaff', $admin->id)
            ->assertSee('tidak dapat menghapus akun Anda sendiri');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(StaffManager::class)
            ->call('toggleStaffStatus', $admin->id)
            ->assertSee('tidak dapat menonaktifkan akun Anda sendiri');

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_last_active_admin_cannot_demote_themselves(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(StaffManager::class)
            ->call('openEditStaffModal', $admin->id)
            ->set('role', UserRole::Cashier->value)
            ->call('saveStaff')
            ->assertHasErrors(['role']);

        $this->assertSame(UserRole::Admin, $admin->fresh()->role);
    }

    public function test_admin_can_step_down_when_another_active_admin_exists(): void
    {
        $admin = $this->admin();
        User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);

        Livewire::actingAs($admin)
            ->test(StaffManager::class)
            ->call('openEditStaffModal', $admin->id)
            ->set('role', UserRole::Cashier->value)
            ->call('saveStaff')
            ->assertHasNoErrors();

        $this->assertSame(UserRole::Cashier, $admin->fresh()->role);
    }

    public function test_admin_can_delete_another_staff_account(): void
    {
        $admin = $this->admin();
        $staff = User::factory()->create(['role' => UserRole::Bar, 'is_active' => true]);

        Livewire::actingAs($admin)
            ->test(StaffManager::class)
            ->call('deleteStaff', $staff->id);

        $this->assertDatabaseMissing('users', ['id' => $staff->id]);
    }

    public function test_staff_list_can_be_searched_and_filtered_by_role(): void
    {
        $admin = $this->admin();
        User::factory()->create(['name' => 'Barista Satu', 'role' => UserRole::Bar, 'is_active' => true]);
        User::factory()->create(['name' => 'Kasir Dua', 'role' => UserRole::Cashier, 'is_active' => true]);

        Livewire::actingAs($admin)
            ->test(StaffManager::class)
            ->set('search', 'Barista')
            ->assertSee('Barista Satu')
            ->assertDontSee('Kasir Dua')
            ->set('search', '')
            ->set('roleFilter', UserRole::Cashier->value)
            ->assertSee('Kasir Dua')
            ->assertDontSee('Barista Satu');
    }
}
