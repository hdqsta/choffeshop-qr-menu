<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\Product;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Modifier Groups & Options
        $groupSize = ModifierGroup::updateOrCreate(
            ['name' => 'Ukuran'],
            ['is_required' => true, 'min_select' => 1, 'max_select' => 1]
        );
        ModifierOption::updateOrCreate(['modifier_group_id' => $groupSize->id, 'name' => 'Regular'], ['price_delta' => 0, 'sort_order' => 1, 'is_available' => true]);
        ModifierOption::updateOrCreate(['modifier_group_id' => $groupSize->id, 'name' => 'Large'], ['price_delta' => 5000, 'sort_order' => 2, 'is_available' => true]);

        $groupTemp = ModifierGroup::updateOrCreate(
            ['name' => 'Suhu / Penyajian'],
            ['is_required' => true, 'min_select' => 1, 'max_select' => 1]
        );
        ModifierOption::updateOrCreate(['modifier_group_id' => $groupTemp->id, 'name' => 'Iced (Dingin)'], ['price_delta' => 0, 'sort_order' => 1, 'is_available' => true]);
        ModifierOption::updateOrCreate(['modifier_group_id' => $groupTemp->id, 'name' => 'Hot (Panas)'], ['price_delta' => 0, 'sort_order' => 2, 'is_available' => true]);

        $groupSugar = ModifierGroup::updateOrCreate(
            ['name' => 'Tingkat Kemanisan'],
            ['is_required' => false, 'min_select' => 0, 'max_select' => 1]
        );
        ModifierOption::updateOrCreate(['modifier_group_id' => $groupSugar->id, 'name' => 'Normal Sugar (100%)'], ['price_delta' => 0, 'sort_order' => 1, 'is_available' => true]);
        ModifierOption::updateOrCreate(['modifier_group_id' => $groupSugar->id, 'name' => 'Less Sugar (50%)'], ['price_delta' => 0, 'sort_order' => 2, 'is_available' => true]);
        ModifierOption::updateOrCreate(['modifier_group_id' => $groupSugar->id, 'name' => 'No Sugar (0%)'], ['price_delta' => 0, 'sort_order' => 3, 'is_available' => true]);

        $groupIce = ModifierGroup::updateOrCreate(
            ['name' => 'Tingkat Es'],
            ['is_required' => false, 'min_select' => 0, 'max_select' => 1]
        );
        ModifierOption::updateOrCreate(['modifier_group_id' => $groupIce->id, 'name' => 'Normal Ice'], ['price_delta' => 0, 'sort_order' => 1, 'is_available' => true]);
        ModifierOption::updateOrCreate(['modifier_group_id' => $groupIce->id, 'name' => 'Less Ice'], ['price_delta' => 0, 'sort_order' => 2, 'is_available' => true]);

        $groupAddon = ModifierGroup::updateOrCreate(
            ['name' => 'Topping / Add-on'],
            ['is_required' => false, 'min_select' => 0, 'max_select' => 3]
        );
        ModifierOption::updateOrCreate(['modifier_group_id' => $groupAddon->id, 'name' => 'Extra Espresso Shot'], ['price_delta' => 5000, 'sort_order' => 1, 'is_available' => true]);
        ModifierOption::updateOrCreate(['modifier_group_id' => $groupAddon->id, 'name' => 'Oat Milk Substitute'], ['price_delta' => 7000, 'sort_order' => 2, 'is_available' => true]);
        ModifierOption::updateOrCreate(['modifier_group_id' => $groupAddon->id, 'name' => 'Caramel Syrup'], ['price_delta' => 4000, 'sort_order' => 3, 'is_available' => true]);
        ModifierOption::updateOrCreate(['modifier_group_id' => $groupAddon->id, 'name' => 'Vanilla Syrup'], ['price_delta' => 4000, 'sort_order' => 4, 'is_available' => true]);

        // 2. Categories & Products
        $catCoffee = Category::updateOrCreate(['name' => 'Coffee'], ['sort_order' => 1, 'is_active' => true]);
        $catNonCoffee = Category::updateOrCreate(['name' => 'Non-Coffee'], ['sort_order' => 2, 'is_active' => true]);
        $catTea = Category::updateOrCreate(['name' => 'Tea & Refreshers'], ['sort_order' => 3, 'is_active' => true]);
        $catPastry = Category::updateOrCreate(['name' => 'Pastry & Snacks'], ['sort_order' => 4, 'is_active' => true]);

        $coffeeProducts = [
            [
                'name' => 'Kopi Susu Gula Aren',
                'description' => 'Espresso house blend, susu segar creamy, dan gula aren organik khas.',
                'price' => 22000,
                'is_available' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Caffe Latte',
                'description' => 'Paduan seimbang espresso kaya aroma dengan steamed milk lembut.',
                'price' => 26000,
                'is_available' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Caramel Macchiato',
                'description' => 'Espresso dengan sirup vanila, susu berbusa, dan saus karamel lezat.',
                'price' => 30000,
                'is_available' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Americano',
                'description' => 'Double shot espresso murni dengan air panas atau es batu segar.',
                'price' => 20000,
                'is_available' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($coffeeProducts as $pData) {
            $product = Product::updateOrCreate(
                ['category_id' => $catCoffee->id, 'name' => $pData['name']],
                $pData
            );
            $product->modifierGroups()->syncWithoutDetaching([
                $groupSize->id => ['sort_order' => 1],
                $groupTemp->id => ['sort_order' => 2],
                $groupSugar->id => ['sort_order' => 3],
                $groupIce->id => ['sort_order' => 4],
                $groupAddon->id => ['sort_order' => 5],
            ]);
        }

        $nonCoffeeProducts = [
            [
                'name' => 'Matcha Latte',
                'description' => 'Matcha murni Uji Jepang dipadukan dengan susu segar premium.',
                'price' => 28000,
                'is_available' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Chocolate Classic',
                'description' => 'Cokelat Belgia pekat dengan rasa manis gurih yang pas.',
                'price' => 25000,
                'is_available' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($nonCoffeeProducts as $pData) {
            $product = Product::updateOrCreate(
                ['category_id' => $catNonCoffee->id, 'name' => $pData['name']],
                $pData
            );
            $product->modifierGroups()->syncWithoutDetaching([
                $groupSize->id => ['sort_order' => 1],
                $groupTemp->id => ['sort_order' => 2],
                $groupSugar->id => ['sort_order' => 3],
                $groupIce->id => ['sort_order' => 4],
            ]);
        }

        $teaProducts = [
            [
                'name' => 'Lychee Berry Tea',
                'description' => 'Teh hitam aromatik dengan buah leci utuh dan sirup beri segar.',
                'price' => 24000,
                'is_available' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Lemon Black Tea',
                'description' => 'Segarnya perasan lemon asli berpadu dengan seduhan daun teh pilihan.',
                'price' => 20000,
                'is_available' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($teaProducts as $pData) {
            $product = Product::updateOrCreate(
                ['category_id' => $catTea->id, 'name' => $pData['name']],
                $pData
            );
            $product->modifierGroups()->syncWithoutDetaching([
                $groupSugar->id => ['sort_order' => 1],
                $groupIce->id => ['sort_order' => 2],
            ]);
        }

        $pastryProducts = [
            [
                'name' => 'Butter Croissant',
                'description' => 'Croissant renyah berlapis dengan mentega Prancis yang harum.',
                'price' => 22000,
                'is_available' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Pain au Chocolat',
                'description' => 'Pastry renyah dengan isian dark chocolate couverture meleleh.',
                'price' => 26000,
                'is_available' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($pastryProducts as $pData) {
            Product::updateOrCreate(
                ['category_id' => $catPastry->id, 'name' => $pData['name']],
                $pData
            );
        }
    }
}
