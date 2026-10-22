<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EnsureRoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'role:cashier'])->get('/test/cashier-only', function () {
            return 'cashier-ok';
        });

        Route::middleware(['web', 'auth', 'role:bar'])->get('/test/bar-only', function () {
            return 'bar-ok';
        });
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/test/cashier-only');

        $response->assertRedirect(route('login'));
    }

    public function test_user_with_matching_role_passes(): void
    {
        $cashier = User::factory()->create([
            'role' => UserRole::Cashier,
            'is_active' => true,
        ]);

        $response = $this->actingAs($cashier)->get('/test/cashier-only');

        $response->assertStatus(200);
        $response->assertSee('cashier-ok');
    }

    public function test_user_with_wrong_role_is_denied_with_403(): void
    {
        $barista = User::factory()->create([
            'role' => UserRole::Bar,
            'is_active' => true,
        ]);

        $response = $this->actingAs($barista)->get('/test/cashier-only');

        $response->assertStatus(403);
    }

    public function test_user_without_any_role_is_denied_with_403(): void
    {
        $roleless = User::factory()->create([
            'role' => null,
            'is_active' => true,
        ]);

        $response = $this->actingAs($roleless)->get('/test/cashier-only');

        $response->assertStatus(403);
    }

    public function test_admin_is_allowed_to_access_any_role(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/test/bar-only');

        $response->assertStatus(200);
        $response->assertSee('bar-ok');
    }

    public function test_inactive_user_is_denied_and_logged_out(): void
    {
        $inactive = User::factory()->create([
            'role' => UserRole::Cashier,
            'is_active' => false,
        ]);

        $response = $this->actingAs($inactive)->get('/test/cashier-only');

        $response->assertStatus(403);
        $this->assertGuest();
    }
}
