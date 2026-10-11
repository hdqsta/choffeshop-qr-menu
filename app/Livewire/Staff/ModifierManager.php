<?php

namespace App\Livewire\Staff;

use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ModifierManager extends Component
{
    // Group Modal state & fields
    public bool $showGroupModal = false;

    public ?int $editingGroupId = null;

    public string $groupName = '';

    public bool $isRequired = false;

    public int $minSelect = 0;

    public int $maxSelect = 1;

    // Option Modal state & fields
    public bool $showOptionModal = false;

    public ?int $targetGroupId = null;

    public ?int $editingOptionId = null;

    public string $optionName = '';

    public int $priceDelta = 0;

    public bool $optionIsAvailable = true;

    public int $optionSortOrder = 0;

    public function openCreateGroupModal(): void
    {
        $this->reset(['editingGroupId', 'groupName', 'isRequired', 'minSelect', 'maxSelect']);
        $this->isRequired = false;
        $this->minSelect = 0;
        $this->maxSelect = 1;
        $this->showGroupModal = true;
    }

    public function openEditGroupModal(int $groupId): void
    {
        $group = ModifierGroup::findOrFail($groupId);
        $this->editingGroupId = $group->id;
        $this->groupName = $group->name;
        $this->isRequired = (bool) $group->is_required;
        $this->minSelect = (int) $group->min_select;
        $this->maxSelect = (int) $group->max_select;
        $this->showGroupModal = true;
    }

    public function saveGroup(): void
    {
        $this->validate([
            'groupName' => 'required|string|max:100',
            'isRequired' => 'boolean',
            'minSelect' => 'integer|min:0',
            'maxSelect' => 'integer|min:1',
        ]);

        if ($this->isRequired && $this->minSelect < 1) {
            $this->minSelect = 1;
        }

        if ($this->minSelect > $this->maxSelect) {
            $this->addError('maxSelect', 'Pilihan maksimal harus lebih besar atau sama dengan minimal pilihan.');

            return;
        }

        $data = [
            'name' => $this->groupName,
            'is_required' => $this->isRequired,
            'min_select' => $this->minSelect,
            'max_select' => $this->maxSelect,
        ];

        if ($this->editingGroupId) {
            $group = ModifierGroup::findOrFail($this->editingGroupId);
            $group->update($data);
            $message = "Grup modifier '{$group->name}' berhasil diperbarui.";
        } else {
            $group = ModifierGroup::create($data);
            $message = "Grup modifier '{$group->name}' berhasil dibuat.";
        }

        $this->showGroupModal = false;
        session()->flash('success', $message);
    }

    public function deleteGroup(int $groupId): void
    {
        $group = ModifierGroup::withCount('products')->findOrFail($groupId);

        if ($group->products_count > 0) {
            session()->flash('error', "Grup modifier '{$group->name}' terhubung ke {$group->products_count} produk. Lepaskan kaitan produk terlebih dahulu.");

            return;
        }

        $name = $group->name;
        $group->delete();
        session()->flash('success', "Grup modifier '{$name}' telah dihapus.");
    }

    // Option methods
    public function openCreateOptionModal(int $groupId): void
    {
        $this->reset(['editingOptionId', 'optionName', 'priceDelta', 'optionIsAvailable', 'optionSortOrder']);
        $this->targetGroupId = $groupId;
        $this->optionIsAvailable = true;
        $this->priceDelta = 0;
        $this->optionSortOrder = 0;
        $this->showOptionModal = true;
    }

    public function openEditOptionModal(int $optionId): void
    {
        $option = ModifierOption::findOrFail($optionId);
        $this->editingOptionId = $option->id;
        $this->targetGroupId = $option->modifier_group_id;
        $this->optionName = $option->name;
        $this->priceDelta = (int) $option->price_delta;
        $this->optionIsAvailable = (bool) $option->is_available;
        $this->optionSortOrder = (int) $option->sort_order;
        $this->showOptionModal = true;
    }

    public function toggleOptionAvailability(int $optionId): void
    {
        $option = ModifierOption::findOrFail($optionId);
        $option->update(['is_available' => ! $option->is_available]);
        session()->flash('success', "Status opsi '{$option->name}' berhasil diperbarui.");
    }

    public function saveOption(): void
    {
        $this->validate([
            'optionName' => 'required|string|max:100',
            'priceDelta' => 'required|integer',
            'optionIsAvailable' => 'boolean',
            'optionSortOrder' => 'integer|min:0',
        ]);

        $data = [
            'modifier_group_id' => $this->targetGroupId,
            'name' => $this->optionName,
            'price_delta' => $this->priceDelta,
            'is_available' => $this->optionIsAvailable,
            'sort_order' => $this->optionSortOrder,
        ];

        if ($this->editingOptionId) {
            $option = ModifierOption::findOrFail($this->editingOptionId);
            $option->update($data);
            $msg = "Opsi modifier '{$option->name}' berhasil diperbarui.";
        } else {
            $option = ModifierOption::create($data);
            $msg = "Opsi modifier '{$option->name}' berhasil ditambahkan.";
        }

        $this->showOptionModal = false;
        session()->flash('success', $msg);
    }

    public function deleteOption(int $optionId): void
    {
        $option = ModifierOption::findOrFail($optionId);
        $name = $option->name;
        $option->delete();
        session()->flash('success', "Opsi modifier '{$name}' telah dihapus.");
    }

    public function render(): View
    {
        $modifierGroups = ModifierGroup::with(['options', 'products'])->get();

        return view('livewire.staff.modifier-manager', [
            'modifierGroups' => $modifierGroups,
        ]);
    }
}
