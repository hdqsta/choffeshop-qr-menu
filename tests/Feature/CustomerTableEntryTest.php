<?php

namespace Tests\Feature;

use App\Models\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTableEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_scanning_valid_table_qr_sets_session_and_redirects_to_menu(): void
    {
        $table = Table::factory()->create([
            'number' => '05',
            'token' => 'tbl_05_validtoken12345',
            'is_active' => true,
        ]);

        $response = $this->get('/t/'.$table->token);

        $response->assertRedirect(route('customer.menu'));
        $response->assertSessionHas('table_id', $table->id);
        $response->assertSessionHas('table_number', '05');
        $response->assertSessionHas('table_token', $table->token);
    }

    public function test_scanning_invalid_token_shows_error_page(): void
    {
        $response = $this->get('/t/invalid-nonexistent-token');

        $response->assertStatus(200);
        $response->assertSee('Meja tidak ditemukan');
    }

    public function test_scanning_inactive_table_shows_error_page(): void
    {
        $table = Table::factory()->create([
            'token' => 'tbl_inactive_token',
            'is_active' => false,
        ]);

        $response = $this->get('/t/'.$table->token);

        $response->assertStatus(200);
        $response->assertSee('tidak aktif');
    }

    public function test_accessing_menu_without_table_session_is_blocked(): void
    {
        $response = $this->get('/menu');

        $response->assertStatus(403);
        $response->assertSee('Scan QR Meja');
    }

    public function test_accessing_menu_with_active_table_session_succeeds(): void
    {
        $table = Table::factory()->create(['is_active' => true]);

        $response = $this->withSession([
            'table_id' => $table->id,
            'table_number' => $table->number,
            'table_token' => $table->token,
        ])->get('/menu');

        $response->assertStatus(200);
    }
}
