<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_route_is_not_registered(): void
    {
        $this->assertFalse(Route::has('register'));
    }

    public function test_registration_screen_is_not_reachable(): void
    {
        $this->get('/register')->assertNotFound();

        $this->assertGuest();
    }

    public function test_visitors_cannot_create_an_account_through_the_register_endpoint(): void
    {
        $response = $this->post('/register', [
            'name' => 'Intruder',
            'email' => 'intruder@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertNotFound();

        $this->assertDatabaseMissing('users', ['email' => 'intruder@example.com']);
        $this->assertSame(0, User::count());
    }
}
