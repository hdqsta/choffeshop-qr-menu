<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Staff\ProductManager;
use App\Models\Category;
use App\Models\ModifierGroup;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_product_manager(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/staff/products');

        $response->assertStatus(200);
        $response->assertSeeLivewire(ProductManager::class);
    }

    public function test_cashier_cannot_access_product_manager(): void
    {
        $cashier = User::factory()->create([
            'role' => UserRole::Cashier,
            'is_active' => true,
        ]);

        $response = $this->actingAs($cashier)->get('/staff/products');

        $response->assertStatus(403);
    }

    public function test_admin_can_toggle_product_availability(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
        $product = Product::factory()->create(['is_available' => true]);

        Livewire::actingAs($admin)
            ->test(ProductManager::class)
            ->call('toggleAvailability', $product->id);

        $this->assertFalse($product->fresh()->is_available);

        Livewire::actingAs($admin)
            ->test(ProductManager::class)
            ->call('toggleAvailability', $product->id);

        $this->assertTrue($product->fresh()->is_available);
    }

    public function test_admin_can_create_product_with_modifier_groups(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
        $category = Category::factory()->create();
        $group = ModifierGroup::factory()->create();

        Livewire::actingAs($admin)
            ->test(ProductManager::class)
            ->call('openCreateProductModal')
            ->set('name', 'Latte Special')
            ->set('category_id', (string) $category->id)
            ->set('price', '28000')
            ->set('description', 'Kopi latte nikmat')
            ->set('is_available', true)
            ->set('sort_order', 1)
            ->set('selectedModifierGroups', [$group->id])
            ->call('saveProduct')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('products', [
            'name' => 'Latte Special',
            'category_id' => $category->id,
            'price' => 28000,
            'is_available' => true,
        ]);

        $product = Product::where('name', 'Latte Special')->first();
        $this->assertCount(1, $product->modifierGroups);
    }

    public function test_admin_can_edit_product(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
        $product = Product::factory()->create(['name' => 'Old Name', 'price' => 20000]);

        Livewire::actingAs($admin)
            ->test(ProductManager::class)
            ->call('openEditProductModal', $product->id)
            ->set('name', 'New Updated Name')
            ->set('price', '25000')
            ->call('saveProduct')
            ->assertHasNoErrors();

        $this->assertSame('New Updated Name', $product->fresh()->name);
        $this->assertSame(25000, $product->fresh()->price);
    }

    public function test_admin_can_delete_product(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
        $product = Product::factory()->create(['name' => 'To Delete']);

        Livewire::actingAs($admin)
            ->test(ProductManager::class)
            ->call('deleteProduct', $product->id);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
