<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class StaffAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_staff_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'email' => 'kasir@coffeeshop.test',
            'password' => bcrypt('password'),
            'role' => UserRole::Cashier,
            'is_active' => true,
        ]);

        $component = Volt::test('pages.auth.login')
            ->set('form.email', 'kasir@coffeeshop.test')
            ->set('form.password', 'password');

        $component->call('login');

        $component
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_staff_cannot_authenticate(): void
    {
        User::factory()->create([
            'email' => 'inactive@coffeeshop.test',
            'password' => bcrypt('password'),
            'role' => UserRole::Cashier,
            'is_active' => false,
        ]);

        $component = Volt::test('pages.auth.login')
            ->set('form.email', 'inactive@coffeeshop.test')
            ->set('form.password', 'password');

        $component->call('login');

        $component->assertHasErrors(['form.email']);
        $this->assertGuest();
    }

    public function test_authenticated_staff_can_view_dashboard_with_role(): void
    {
        $barista = User::factory()->create([
            'name' => 'Budi Barista',
            'role' => UserRole::Bar,
            'is_active' => true,
        ]);

        $response = $this->actingAs($barista)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Budi Barista');
        $response->assertSee('Barista');
    }
}
