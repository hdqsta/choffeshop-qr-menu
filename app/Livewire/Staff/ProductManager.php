<?php

namespace App\Livewire\Staff;

use App\Models\Category;
use App\Models\ModifierGroup;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ProductManager extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public ?int $selectedCategory = null;

    // Product modal state & fields
    public bool $showProductModal = false;

    public ?int $editingProductId = null;

    public string $name = '';

    public string $category_id = '';

    public string $price = '';

    public string $description = '';

    public bool $is_available = true;

    public int $sort_order = 0;

    /** @var array<int> */
    public array $selectedModifierGroups = [];

    public mixed $image = null;

    public ?string $existingImage = null;

    // Category modal state & fields
    public bool $showCategoryModal = false;

    public ?int $editingCategoryId = null;

    public string $categoryName = '';

    public int $categorySortOrder = 0;

    public bool $categoryIsActive = true;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'is_available' => 'boolean',
            'sort_order' => 'integer|min:0',
            'selectedModifierGroups' => 'array',
            'selectedModifierGroups.*' => 'exists:modifier_groups,id',
            'image' => 'nullable|image|max:2048',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedCategory(): void
    {
        $this->resetPage();
    }

    public function toggleAvailability(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $product->update([
            'is_available' => ! $product->is_available,
        ]);

        session()->flash('success', "Status produk '{$product->name}' berhasil diperbarui.");
    }

    public function openCreateProductModal(): void
    {
        $this->reset([
            'editingProductId',
            'name',
            'category_id',
            'price',
            'description',
            'is_available',
            'sort_order',
            'selectedModifierGroups',
            'image',
            'existingImage',
        ]);
        $this->is_available = true;
        $this->sort_order = 0;

        // Auto select first category if available
        $firstCategory = Category::orderBy('sort_order')->first();
        if ($firstCategory) {
            $this->category_id = (string) $firstCategory->id;
        }

        $this->showProductModal = true;
    }

    public function openEditProductModal(int $productId): void
    {
        $product = Product::with('modifierGroups')->findOrFail($productId);

        $this->editingProductId = $product->id;
        $this->name = $product->name;
        $this->category_id = (string) $product->category_id;
        $this->price = (string) $product->price;
        $this->description = (string) $product->description;
        $this->is_available = (bool) $product->is_available;
        $this->sort_order = (int) $product->sort_order;
        $this->selectedModifierGroups = $product->modifierGroups->pluck('id')->all();
        $this->existingImage = $product->image;
        $this->image = null;

        $this->showProductModal = true;
    }

    public function saveProduct(): void
    {
        $this->validate();

        $imagePath = $this->existingImage;
        if ($this->image) {
            if ($this->existingImage) {
                Storage::disk('public')->delete($this->existingImage);
            }
            $imagePath = $this->image->store('products', 'public');
        }

        $data = [
            'name' => $this->name,
            'category_id' => (int) $this->category_id,
            'price' => (int) $this->price,
            'description' => $this->description ?: null,
            'is_available' => $this->is_available,
            'sort_order' => $this->sort_order,
            'image' => $imagePath,
        ];

        if ($this->editingProductId) {
            $product = Product::findOrFail($this->editingProductId);
            $product->update($data);
            $message = "Produk '{$product->name}' berhasil diperbarui.";
        } else {
            $product = Product::create($data);
            $message = "Produk '{$product->name}' berhasil ditambahkan.";
        }

        // Sync modifier groups
        $syncData = [];
        foreach ($this->selectedModifierGroups as $index => $groupId) {
            $syncData[$groupId] = ['sort_order' => $index + 1];
        }
        $product->modifierGroups()->sync($syncData);

        $this->showProductModal = false;
        session()->flash('success', $message);
    }

    public function deleteProduct(int $productId): void
    {
        $product = Product::findOrFail($productId);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $name = $product->name;
        $product->delete();

        session()->flash('success', "Produk '{$name}' telah dihapus.");
    }

    // Category Management
    public function openCategoryModal(): void
    {
        $this->reset(['editingCategoryId', 'categoryName', 'categorySortOrder', 'categoryIsActive']);
        $this->categoryIsActive = true;
        $this->showCategoryModal = true;
    }

    public function openEditCategory(int $categoryId): void
    {
        $category = Category::findOrFail($categoryId);
        $this->editingCategoryId = $category->id;
        $this->categoryName = $category->name;
        $this->categorySortOrder = $category->sort_order;
        $this->categoryIsActive = $category->is_active;
        $this->showCategoryModal = true;
    }

    public function saveCategory(): void
    {
        $this->validate([
            'categoryName' => 'required|string|max:100',
            'categorySortOrder' => 'integer|min:0',
            'categoryIsActive' => 'boolean',
        ]);

        if ($this->editingCategoryId) {
            $category = Category::findOrFail($this->editingCategoryId);
            $category->update([
                'name' => $this->categoryName,
                'sort_order' => $this->categorySortOrder,
                'is_active' => $this->categoryIsActive,
            ]);
            $msg = 'Kategori berhasil diperbarui.';
        } else {
            Category::create([
                'name' => $this->categoryName,
                'sort_order' => $this->categorySortOrder,
                'is_active' => $this->categoryIsActive,
            ]);
            $msg = 'Kategori baru berhasil ditambahkan.';
        }

        $this->showCategoryModal = false;
        session()->flash('success', $msg);
    }

    public function deleteCategory(int $categoryId): void
    {
        $category = Category::withCount('products')->findOrFail($categoryId);

        if ($category->products_count > 0) {
            session()->flash('error', "Kategori '{$category->name}' tidak bisa dihapus karena masih memiliki {$category->products_count} produk.");

            return;
        }

        $category->delete();
        session()->flash('success', 'Kategori berhasil dihapus.');
    }

    public function render(): View
    {
        $categories = Category::ordered()->withCount('products')->get();
        $modifierGroups = ModifierGroup::with('options')->get();

        $productsQuery = Product::with(['category', 'modifierGroups'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%");
            })
            ->when($this->selectedCategory, function ($query) {
                $query->where('category_id', $this->selectedCategory);
            })
            ->orderBy('category_id')
            ->orderBy('sort_order')
            ->orderBy('name');

        $products = $productsQuery->paginate(12);

        return view('livewire.staff.product-manager', [
            'categories' => $categories,
            'products' => $products,
            'modifierGroups' => $modifierGroups,
        ]);
    }
}
