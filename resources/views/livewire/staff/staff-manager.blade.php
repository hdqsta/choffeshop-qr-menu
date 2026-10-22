<div class="py-8 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Top Header & Actions -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Manajemen Akun Staf
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Pendaftaran publik ditutup. Buat akun kasir, barista, dan admin dari halaman ini, lalu atur perannya.
                </p>
            </div>
            <div>
                <button wire:click="openCreateStaffModal" class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-semibold transition shadow-sm">
                    <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    Tambah Akun Staf
                </button>
            </div>
        </div>

        <!-- Flash Alerts -->
        @if (session()->has('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if ($activeAdminCount <= 1)
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-sm shadow-sm">
                Hanya ada <strong>{{ $activeAdminCount }}</strong> admin aktif. Tambahkan admin kedua agar pengaturan tetap bisa diubah bila akun ini bermasalah.
            </div>
        @endif

        <!-- Filter Bar & Search -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                <button
                    wire:click="$set('roleFilter', '')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-medium transition {{ $roleFilter === '' ? 'bg-amber-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Semua Peran
                </button>

                @foreach ($roles as $roleOption)
                    <button
                        wire:key="role-filter-{{ $roleOption->value }}"
                        wire:click="$set('roleFilter', '{{ $roleOption->value }}')"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-medium transition {{ $roleFilter === $roleOption->value ? 'bg-amber-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        {{ $roleOption->label() }}
                    </button>
                @endforeach
            </div>

            <div class="relative w-full md:w-72">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Cari nama atau email..."
                    class="w-full text-sm border-gray-200 rounded-xl focus:ring-amber-500 focus:border-amber-500 ps-9" />
                <svg class="w-4 h-4 text-gray-400 absolute start-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                </svg>
            </div>
        </div>

        <!-- Staff List -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-[11px] uppercase tracking-wider text-gray-500">
                            <th class="px-5 py-3 font-semibold">Nama & Email</th>
                            <th class="px-5 py-3 font-semibold">Peran</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 font-semibold">Terakhir Diperbarui</th>
                            <th class="px-5 py-3 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($staffMembers as $member)
                            <tr wire:key="staff-row-{{ $member->id }}" class="hover:bg-gray-50/70 transition">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900 flex items-center gap-2">
                                        {{ $member->name }}
                                        @if ($member->is(auth()->user()))
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-lg">Anda</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $member->email }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold
                                        @if ($member->role === \App\Enums\UserRole::Admin) bg-indigo-50 text-indigo-700 border border-indigo-200
                                        @elseif ($member->role === \App\Enums\UserRole::Cashier) bg-emerald-50 text-emerald-700 border border-emerald-200
                                        @elseif ($member->role === \App\Enums\UserRole::Bar) bg-blue-50 text-blue-700 border border-blue-200
                                        @elseif ($member->role === \App\Enums\UserRole::Waiter) bg-stone-100 text-stone-700 border border-stone-200
                                        @else bg-rose-50 text-rose-700 border border-rose-200
                                        @endif">
                                        {{ $member->roleLabel() }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <button
                                        wire:click="toggleStaffStatus({{ $member->id }})"
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold transition {{ $member->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                        {{ $member->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </td>
                                <td class="px-5 py-4 text-xs text-gray-500">
                                    {{ $member->updated_at?->format('d/m/Y H:i') ?? '-' }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-3 text-xs">
                                        <button wire:click="openEditStaffModal({{ $member->id }})" class="text-indigo-600 hover:underline font-semibold">Edit</button>
                                        <span class="text-gray-300">|</span>
                                        <button
                                            wire:click="deleteStaff({{ $member->id }})"
                                            wire:confirm="Hapus akun {{ $member->name }}?"
                                            class="text-rose-600 hover:underline font-semibold">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center">
                                    <h3 class="text-sm font-semibold text-gray-900">Belum ada akun staf yang cocok</h3>
                                    <p class="mt-1 text-xs text-gray-500">Ubah pencarian atau tambahkan akun staf baru.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($staffMembers->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">
                    {{ $staffMembers->links() }}
                </div>
            @endif
        </div>

        <!-- Staff Modal -->
        @if ($showStaffModal)
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div wire:click="$set('showStaffModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                    <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                        <form wire:submit="saveStaff">
                            <div class="p-6 space-y-4">
                                <div class="flex items-center justify-between border-b pb-3">
                                    <h3 class="text-lg font-bold text-gray-900">
                                        {{ $editingStaffId ? 'Edit Akun Staf' : 'Tambah Akun Staf' }}
                                    </h3>
                                    <button type="button" wire:click="$set('showStaffModal', false)" class="text-gray-400 hover:text-gray-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Nama Lengkap *</label>
                                    <input wire:model="name" type="text" placeholder="Contoh: Sari Kasir" class="w-full text-sm border-gray-200 rounded-xl focus:ring-amber-500 focus:border-amber-500" required />
                                    @error('name') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Email *</label>
                                    <input wire:model="email" type="email" placeholder="nama@coffeeshop.test" class="w-full text-sm border-gray-200 rounded-xl focus:ring-amber-500 focus:border-amber-500" required />
                                    @error('email') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Peran *</label>
                                    <select wire:model="role" class="w-full text-sm border-gray-200 rounded-xl focus:ring-amber-500 focus:border-amber-500" required>
                                        <option value="">— Pilih peran —</option>
                                        @foreach ($roles as $roleOption)
                                            <option value="{{ $roleOption->value }}">{{ $roleOption->label() }}</option>
                                        @endforeach
                                    </select>
                                    @error('role') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                            Kata Sandi {{ $editingStaffId ? '(opsional)' : '*' }}
                                        </label>
                                        <input wire:model="password" type="password" autocomplete="new-password" class="w-full text-sm border-gray-200 rounded-xl focus:ring-amber-500 focus:border-amber-500" />
                                        @error('password') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Ulangi Kata Sandi</label>
                                        <input wire:model="password_confirmation" type="password" autocomplete="new-password" class="w-full text-sm border-gray-200 rounded-xl focus:ring-amber-500 focus:border-amber-500" />
                                    </div>
                                </div>

                                @if ($editingStaffId)
                                    <p class="text-[11px] text-gray-500">Biarkan kata sandi kosong bila tidak ingin mengubahnya.</p>
                                @endif

                                <div class="pt-1">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input wire:model="is_active" type="checkbox" class="rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500">
                                        <span class="ms-2 text-xs font-semibold text-gray-700">Akun Aktif (bisa login)</span>
                                    </label>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-6 py-3 flex items-center justify-end gap-2 border-t">
                                <button type="button" wire:click="$set('showStaffModal', false)" class="px-4 py-2 border rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    Batal
                                </button>
                                <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-semibold shadow-sm">
                                    Simpan Akun
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
