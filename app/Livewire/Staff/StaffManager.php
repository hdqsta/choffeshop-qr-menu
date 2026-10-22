<?php

namespace App\Livewire\Staff;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class StaffManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $roleFilter = '';

    public bool $showStaffModal = false;

    public ?int $editingStaffId = null;

    public string $name = '';

    public string $email = '';

    public string $role = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $is_active = true;

    /**
     * Livewire actions are not covered by the route middleware, so the
     * component guards itself against non-admins.
     */
    public function mount(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'Hanya admin yang dapat mengelola akun staf.');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                Rule::unique(User::class)->ignore($this->editingStaffId),
            ],
            'role' => ['required', Rule::enum(UserRole::class)],
            'password' => [
                $this->editingStaffId ? 'nullable' : 'required',
                'string',
                'confirmed',
                Password::defaults(),
            ],
            'is_active' => ['boolean'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateStaffModal(): void
    {
        $this->reset([
            'editingStaffId',
            'name',
            'email',
            'role',
            'password',
            'password_confirmation',
            'is_active',
        ]);

        $this->is_active = true;
        $this->showStaffModal = true;
    }

    public function openEditStaffModal(int $staffId): void
    {
        $staff = User::findOrFail($staffId);

        $this->editingStaffId = $staff->id;
        $this->name = $staff->name;
        $this->email = $staff->email;
        $this->role = $staff->role?->value ?? '';
        $this->is_active = (bool) $staff->is_active;
        $this->password = '';
        $this->password_confirmation = '';
        $this->resetValidation();

        $this->showStaffModal = true;
    }

    public function saveStaff(): void
    {
        $this->validate();

        $role = UserRole::from($this->role);

        if ($this->editingStaffId) {
            $staff = User::findOrFail($this->editingStaffId);

            if ($staff->is(auth()->user()) && ! $this->is_active) {
                $this->addError('is_active', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');

                return;
            }

            $losesAdminRole = $staff->role === UserRole::Admin
                && ($role !== UserRole::Admin || ! $this->is_active);

            if ($losesAdminRole && $this->isLastActiveAdmin($staff)) {
                $this->addError('role', 'Ini satu-satunya admin aktif. Angkat admin lain sebelum mengubah peran akun ini.');

                return;
            }

            $staff->fill([
                'name' => $this->name,
                'email' => $this->email,
                'role' => $role,
                'is_active' => $this->is_active,
            ]);

            if ($this->password !== '') {
                $staff->password = $this->password;
                $staff->remember_token = null;
            }

            $staff->save();
            $message = "Akun '{$staff->name}' berhasil diperbarui.";
        } else {
            $staff = new User([
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
                'role' => $role,
                'is_active' => $this->is_active,
            ]);

            // Staff accounts are provisioned by an admin, so the address is
            // trusted and is marked verified without an e-mail round trip.
            // `email_verified_at` is intentionally kept out of the model's
            // fillable list and assigned explicitly here.
            $staff->email_verified_at = now();
            $staff->save();

            $message = "Akun staf '{$staff->name}' berhasil dibuat.";
        }

        $this->showStaffModal = false;
        session()->flash('success', $message);
    }

    public function toggleStaffStatus(int $staffId): void
    {
        $staff = User::findOrFail($staffId);

        if ($staff->is(auth()->user())) {
            session()->flash('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');

            return;
        }

        $staff->update(['is_active' => ! $staff->is_active]);

        $state = $staff->is_active ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('success', "Akun '{$staff->name}' berhasil {$state}.");
    }

    public function deleteStaff(int $staffId): void
    {
        $staff = User::findOrFail($staffId);

        if ($staff->is(auth()->user())) {
            session()->flash('error', 'Anda tidak dapat menghapus akun Anda sendiri.');

            return;
        }

        $name = $staff->name;
        $staff->delete();

        session()->flash('success', "Akun '{$name}' telah dihapus.");
    }

    /**
     * True when the account is the only active admin left, so demoting or
     * deactivating it would lock everybody out of the admin area.
     */
    private function isLastActiveAdmin(User $staff): bool
    {
        if ($staff->role !== UserRole::Admin || ! $staff->is_active) {
            return false;
        }

        return User::query()
            ->where('role', UserRole::Admin)
            ->where('is_active', true)
            ->whereKeyNot($staff->id)
            ->doesntExist();
    }

    public function render(): View
    {
        $staff = User::query()
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($nested): void {
                    $nested->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->roleFilter !== '', fn ($query) => $query->where('role', $this->roleFilter))
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.staff.staff-manager', [
            'staffMembers' => $staff,
            'roles' => UserRole::cases(),
            'activeAdminCount' => User::where('role', UserRole::Admin)->where('is_active', true)->count(),
        ]);
    }
}
