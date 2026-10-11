<div class="py-8 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Top Header & Actions -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    Manajemen Modifier & Varian
                </h1>
                <p class="text-sm text-gray-500 mt-1">Atur kustomisasi produk seperti ukuran, tingkat kemanisan, suhu sajian, dan topping tambahan.</p>
            </div>
            <div>
                <button wire:click="openCreateGroupModal" class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-semibold transition shadow-sm">
                    <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Grup Modifier
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

        <!-- Groups Cards List -->
        <div class="space-y-6">
            @forelse ($modifierGroups as $group)
                <div wire:key="group-card-{{ $group->id }}" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <!-- Group Header -->
                    <div class="p-5 bg-gradient-to-r from-amber-50/50 to-white border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-3">
                                <h3 class="text-lg font-bold text-gray-900">{{ $group->name }}</h3>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $group->is_required ? 'bg-rose-100 text-rose-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $group->is_required ? 'Wajib Dipilih' : 'Opsional' }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                    Pilih {{ $group->min_select }} - {{ $group->max_select }} opsi
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Digunakan pada <span class="font-semibold text-gray-700">{{ $group->products->count() }}</span> produk menu
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <button
                                wire:click="openCreateOptionModal({{ $group->id }})"
                                class="inline-flex items-center px-3 py-1.5 bg-amber-600/10 hover:bg-amber-600/20 text-amber-800 rounded-xl text-xs font-semibold transition">
                                <svg class="w-3.5 h-3.5 me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Tambah Opsi
                            </button>
                            <button
                                wire:click="openEditGroupModal({{ $group->id }})"
                                class="px-3 py-1.5 border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl text-xs font-medium transition">
                                Edit Grup
                            </button>
                            <button
                                wire:click="deleteGroup({{ $group->id }})"
                                wire:confirm="Hapus grup modifier '{{ $group->name }}'?"
                                class="px-3 py-1.5 border border-rose-200 hover:bg-rose-50 text-rose-600 rounded-xl text-xs font-medium transition">
                                Hapus
                            </button>
                        </div>
                    </div>

                    <!-- Options Table -->
                    <div class="p-5">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100 text-sm">
                                <thead>
                                    <tr class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                        <th class="pb-3 w-16">Urutan</th>
                                        <th class="pb-3">Nama Opsi Varian</th>
                                        <th class="pb-3">Perubahan Harga</th>
                                        <th class="pb-3">Ketersediaan</th>
                                        <th class="pb-3 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($group->options as $option)
                                        <tr wire:key="opt-row-{{ $option->id }}" class="hover:bg-gray-50/50 transition">
                                            <td class="py-3 text-xs text-gray-400 font-mono">#{{ $option->sort_order }}</td>
                                            <td class="py-3 font-medium text-gray-900">{{ $option->name }}</td>
                                            <td class="py-3">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold {{ $option->price_delta > 0 ? 'bg-amber-100 text-amber-800' : ($option->price_delta < 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700') }}">
                                                    {{ $option->formatted_price_delta }}
                                                </span>
                                            </td>
                                            <td class="py-3">
                                                <button
                                                    wire:click="toggleOptionAvailability({{ $option->id }})"
                                                    class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold cursor-pointer transition {{ $option->is_available ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-rose-100 text-rose-800 hover:bg-rose-200' }}">
                                                    {{ $option->is_available ? 'Tersedia' : 'Habis' }}
                                                </button>
                                            </td>
                                            <td class="py-3 text-right space-x-2">
                                                <button wire:click="openEditOptionModal({{ $option->id }})" class="text-xs text-indigo-600 hover:underline font-semibold">Edit</button>
                                                <button wire:click="deleteOption({{ $option->id }})" wire:confirm="Hapus opsi '{{ $option->name }}'?" class="text-xs text-rose-600 hover:underline font-semibold">Hapus</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-6 text-center text-xs text-gray-400 italic">
                                                Belum ada opsi pada grup ini. Klik "Tambah Opsi" untuk menambahkan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white p-12 text-center rounded-2xl border border-gray-100 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-900">Belum ada grup modifier</h3>
                    <p class="mt-1 text-xs text-gray-500">Buat grup seperti "Ukuran", "Level Gula", atau "Add-on" untuk menu minuman Anda.</p>
                </div>
            @endforelse
        </div>

        <!-- Group Modal -->
        @if ($showGroupModal)
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div wire:click="$set('showGroupModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                    <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-100">
                        <form wire:submit="saveGroup">
                            <div class="p-6 space-y-4">
                                <div class="flex items-center justify-between border-b pb-3">
                                    <h3 class="text-lg font-bold text-gray-900">
                                        {{ $editingGroupId ? 'Edit Grup Modifier' : 'Tambah Grup Modifier Baru' }}
                                    </h3>
                                    <button type="button" wire:click="$set('showGroupModal', false)" class="text-gray-400 hover:text-gray-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Nama Grup Modifier *</label>
                                    <input wire:model="groupName" type="text" placeholder="Contoh: Tingkat Kemanisan" class="w-full text-sm border-gray-200 rounded-xl focus:ring-amber-500 focus:border-amber-500" required />
                                    @error('groupName') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                                </div>

                                <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input wire:model="isRequired" type="checkbox" class="rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500">
                                        <span class="ms-2 text-xs font-bold text-gray-800">Wajib Dipilih Oleh Pelanggan</span>
                                    </label>
                                    <p class="text-[11px] text-gray-500 mt-1">Jika dicentang, customer tidak bisa melanjutkan sebelum memilih opsi dalam grup ini.</p>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Min. Pilihan</label>
                                        <input wire:model="minSelect" type="number" min="0" class="w-full text-sm border-gray-200 rounded-xl focus:ring-amber-500 focus:border-amber-500" required />
                                        @error('minSelect') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Maks. Pilihan</label>
                                        <input wire:model="maxSelect" type="number" min="1" class="w-full text-sm border-gray-200 rounded-xl focus:ring-amber-500 focus:border-amber-500" required />
                                        @error('maxSelect') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-6 py-3 flex items-center justify-end gap-2 border-t">
                                <button type="button" wire:click="$set('showGroupModal', false)" class="px-4 py-2 border rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    Batal
                                </button>
                                <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-semibold shadow-sm">
                                    Simpan Grup
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <!-- Option Modal -->
        @if ($showOptionModal)
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div wire:click="$set('showOptionModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                    <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-100">
                        <form wire:submit="saveOption">
                            <div class="p-6 space-y-4">
                                <div class="flex items-center justify-between border-b pb-3">
                                    <h3 class="text-lg font-bold text-gray-900">
                                        {{ $editingOptionId ? 'Edit Opsi Modifier' : 'Tambah Opsi Modifier Baru' }}
                                    </h3>
                                    <button type="button" wire:click="$set('showOptionModal', false)" class="text-gray-400 hover:text-gray-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Nama Opsi *</label>
                                    <input wire:model="optionName" type="text" placeholder="Contoh: Less Sugar (50%)" class="w-full text-sm border-gray-200 rounded-xl focus:ring-amber-500 focus:border-amber-500" required />
                                    @error('optionName') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Perubahan Harga (Rp)</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs text-gray-500 font-semibold">Rp</span>
                                        <input wire:model="priceDelta" type="number" step="1000" placeholder="0 (Isi 0 jika gratis)" class="w-full text-sm pl-9 border-gray-200 rounded-xl focus:ring-amber-500 focus:border-amber-500" required />
                                    </div>
                                    <p class="text-[11px] text-gray-500 mt-1">Gunakan angka positif untuk biaya tambahan (cth: 5000), atau 0 jika tidak ada biaya.</p>
                                    @error('priceDelta') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                                </div>

                                <div class="grid grid-cols-2 gap-3 items-center pt-2">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Urutan Tampil</label>
                                        <input wire:model="optionSortOrder" type="number" min="0" class="w-full text-sm border-gray-200 rounded-xl focus:ring-amber-500 focus:border-amber-500" />
                                    </div>
                                    <div class="pt-5">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input wire:model="optionIsAvailable" type="checkbox" class="rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500">
                                            <span class="ms-2 text-xs font-semibold text-gray-700">Tersedia</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-6 py-3 flex items-center justify-end gap-2 border-t">
                                <button type="button" wire:click="$set('showOptionModal', false)" class="px-4 py-2 border rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    Batal
                                </button>
                                <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-semibold shadow-sm">
                                    Simpan Opsi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
