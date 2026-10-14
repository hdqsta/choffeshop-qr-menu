<?php

namespace App\Livewire\Customer;

use App\Models\Category;
use App\Models\Product;
use App\Services\Cart\CartService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.customer')]
class Menu extends Component
{
    public string $search = '';

    public ?int $activeCategoryId = null;

    // Product Customization Modal
    public bool $showModal = false;

    public ?Product $selectedProduct = null;

    /** @var array<int, int|array<int>> Group ID => Option ID(s) */
    public array $selectedModifiers = [];

    public int $quantity = 1;

    public string $notes = '';

    public function selectCategory(?int $categoryId): void
    {
        $this->activeCategoryId = $categoryId;
    }

    public function openProductModal(int $productId): void
    {
        $product = Product::with(['category', 'modifierGroups.options' => function ($q) {
            $q->where('is_available', true)->orderBy('sort_order');
        }])->findOrFail($productId);

        if (! $product->is_available) {
            return;
        }

        $this->selectedProduct = $product;
        $this->quantity = 1;
        $this->notes = '';
        $this->selectedModifiers = [];

        // Pre-select first option for required single-choice modifier groups
        foreach ($product->modifierGroups as $group) {
            if ($group->is_required && $group->max_select === 1 && $group->options->isNotEmpty()) {
                $this->selectedModifiers[$group->id] = $group->options->first()->id;
            } else {
                $this->selectedModifiers[$group->id] = [];
            }
        }

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedProduct = null;
    }

    public function incrementQuantity(): void
    {
        $this->quantity++;
    }

    public function decrementQuantity(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    /**
     * Calculate current total price inside the modal.
     */
    public function getModalTotalPriceProperty(): int
    {
        if (! $this->selectedProduct) {
            return 0;
        }

        $total = (int) $this->selectedProduct->price;

        foreach ($this->selectedProduct->modifierGroups as $group) {
            $selection = $this->selectedModifiers[$group->id] ?? null;

            if (is_array($selection)) {
                foreach ($selection as $optId) {
                    $option = $group->options->firstWhere('id', $optId);
                    if ($option) {
                        $total += (int) $option->price_delta;
                    }
                }
            } elseif ($selection) {
                $option = $group->options->firstWhere('id', $selection);
                if ($option) {
                    $total += (int) $option->price_delta;
                }
            }
        }

        return $total * $this->quantity;
    }

    public function addToCart(CartService $cartService): void
    {
        if (! $this->selectedProduct || ! $this->selectedProduct->is_available) {
            return;
        }

        // Validate required modifier groups
        foreach ($this->selectedProduct->modifierGroups as $group) {
            $selection = $this->selectedModifiers[$group->id] ?? null;

            if ($group->is_required) {
                if (empty($selection)) {
                    $this->addError("modifiers.{$group->id}", "Silakan pilih {$group->name}.");

                    return;
                }
            }

            if (is_array($selection) && count($selection) > $group->max_select) {
                $this->addError("modifiers.{$group->id}", "Maksimal pilih {$group->max_select} opsi untuk {$group->name}.");

                return;
            }
        }

        // Collect all chosen option IDs
        $optionIds = [];
        foreach ($this->selectedModifiers as $val) {
            if (is_array($val)) {
                foreach ($val as $id) {
                    if ($id) {
                        $optionIds[] = (int) $id;
                    }
                }
            } elseif ($val) {
                $optionIds[] = (int) $val;
            }
        }

        $cartService->add(
            $this->selectedProduct,
            $optionIds,
            $this->quantity,
            $this->notes
        );

        $this->closeModal();
        $this->dispatch('cart-updated');
    }

    public function render(CartService $cartService): View
    {
        $categories = Category::active()
            ->ordered()
            ->with(['products' => function ($query) {
                $query->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                    ->orderBy('sort_order')
                    ->orderBy('name');
            }])
            ->get();

        return view('livewire.customer.menu', [
            'categories' => $categories,
            'cartCount' => $cartService->count(),
            'cartTotal' => $cartService->total(),
        ]);
    }
}
