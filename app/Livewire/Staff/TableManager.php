<?php

namespace App\Livewire\Staff;

use App\Models\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class TableManager extends Component
{
    public bool $showTableModal = false;

    public ?int $editingTableId = null;

    public string $number = '';

    public bool $isActive = true;

    // QR Preview modal
    public bool $showQrModal = false;

    public ?int $previewTableId = null;

    public function openCreateTableModal(): void
    {
        $this->reset(['editingTableId', 'number', 'isActive']);
        $this->isActive = true;
        $this->showTableModal = true;
    }

    public function openEditTableModal(int $tableId): void
    {
        $table = Table::findOrFail($tableId);
        $this->editingTableId = $table->id;
        $this->number = $table->number;
        $this->isActive = (bool) $table->is_active;
        $this->showTableModal = true;
    }

    public function toggleTableStatus(int $tableId): void
    {
        $table = Table::findOrFail($tableId);
        $table->update(['is_active' => ! $table->is_active]);
        session()->flash('success', "Status Meja {$table->number} berhasil diperbarui.");
    }

    public function regenerateToken(int $tableId): void
    {
        $table = Table::findOrFail($tableId);
        $table->update([
            'token' => 'tbl_'.strtolower($table->number).'_'.Str::random(20),
        ]);
        session()->flash('success', "Token QR untuk Meja {$table->number} berhasil diperbarui.");
    }

    public function openQrPreview(int $tableId): void
    {
        $this->previewTableId = $tableId;
        $this->showQrModal = true;
    }

    public function saveTable(): void
    {
        $this->validate([
            'number' => 'required|string|max:20',
            'isActive' => 'boolean',
        ]);

        if ($this->editingTableId) {
            $table = Table::findOrFail($this->editingTableId);
            $table->update([
                'number' => $this->number,
                'is_active' => $this->isActive,
            ]);
            $msg = "Meja {$table->number} berhasil diperbarui.";
        } else {
            $table = Table::create([
                'number' => $this->number,
                'token' => 'tbl_'.strtolower($this->number).'_'.Str::random(20),
                'is_active' => $this->isActive,
            ]);
            $msg = "Meja {$table->number} berhasil ditambahkan.";
        }

        $this->showTableModal = false;
        session()->flash('success', $msg);
    }

    public function deleteTable(int $tableId): void
    {
        $table = Table::withCount('orders')->findOrFail($tableId);

        if ($table->orders_count > 0) {
            session()->flash('error', "Meja {$table->number} memiliki riwayat {$table->orders_count} pesanan. Nonaktifkan meja daripada menghapusnya.");

            return;
        }

        $num = $table->number;
        $table->delete();
        session()->flash('success', "Meja {$num} telah dihapus.");
    }

    public function render(): View
    {
        $tables = Table::withCount('orders')->orderBy('number')->get();
        $previewTable = $this->previewTableId ? Table::find($this->previewTableId) : null;

        return view('livewire.staff.table-manager', [
            'tables' => $tables,
            'previewTable' => $previewTable,
        ]);
    }
}
