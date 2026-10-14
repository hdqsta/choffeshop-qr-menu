<div class="py-8 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Top Header & Actions -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Manajemen Menu & Kategori
                </h1>
                <p class="text-sm text-gray-500 mt-1">Kelola katalog produk, harga, ketersediaan stok, dan grup modifier.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button wire:click="openCategoryModal" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">
                    <svg class="w-4 h-4 me-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Kelola Kategori
                </button>
                <button wire:click="openCreateProductModal" class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-semibold transition shadow-sm">
                    <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Produk Baru
                </button>
            </div>
        </div>

        <!-- Flash Alert -->
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

        <!-- Filter Bar & Search -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
            <!-- Category Pills -->
            <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                <button
                    wire:click="$set('selectedCategory', null)"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-medium transition {{ is_null($selectedCategory) ? 'bg-amber-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Semua ({{ \App\Models\Product::count() }})
                </button>
                @foreach ($categories as $cat)
                    <button
                        wire:key="cat-btn-{{ $cat->id }}"
                        wire:click="$set('selectedCategory', {{ $cat->id }})"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-medium transition {{ $selectedCategory === $cat->id ? 'bg-amber-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        {{ $cat->name }} ({{ $cat->products_count }})
                    </button>
                @endforeach
            </div>

            <!-- Search Input -->
            <div class="relative w-full md:w-72">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Cari nama menu..."
                    class="w-full text-sm pl-9 pr-4 py-2 border border-gray-200 rounded-xl focus:border-amber-500 focus:ring-amber-500 transition" />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Product Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @forelse ($products as $product)
                <div wire:key="prod-{{ $product->id }}" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <!-- Image Container -->
                        <div class="relative h-44 w-full bg-amber-50 overflow-hidden">
                            @if ($product->image)
                                <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center text-amber-300">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    <span class="text-xs text-amber-500/70 mt-1">Tanpa Foto</span>
                                </div>
                            @endif

                            <!-- Category Badge -->
                            <span class="absolute top-3 left-3 bg-white/90 backdrop-blur-md text-gray-800 text-xs font-medium px-2.5 py-1 rounded-lg shadow-sm">
                                {{ $product->category->name }}
                            </span>

                            <!-- Availability Badge -->
                            <div class="absolute top-3 right-3">
                                <button
                                    wire:click="toggleAvailability({{ $product->id }})"
                                    title="Klik untuk ubah ketersediaan"
                                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold shadow-sm transition {{ $product->is_available ? 'bg-emerald-500 text-white hover:bg-emerald-600' : 'bg-rose-500 text-white hover:bg-rose-600' }}">
                                    {{ $product->is_available ? 'Tersedia' : 'Habis' }}
                                </button>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-4 space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-bold text-gray-900 text-base leading-snug line-clamp-1">{{ $product->name }}</h3>
                                <span class="font-bold text-amber-600 text-sm whitespace-nowrap">{{ $product->formatted_price }}</span>
                            </div>

                            @if ($product->description)
                                <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed">{{ $product->description }}</p>
                            @endif

                            <!-- Modifiers Attached -->
                            <div class="pt-2">
                                <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Grup Modifier:</div>
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($product->modifierGroups as $group)
                                        <span wire:key="mg-tag-{{ $group->id }}" class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-amber-50 text-amber-800 border border-amber-200/50">
                                            {{ $group->name }}
                                        </span>
                                    @empty
                                        <span class="text-[11px] text-gray-400 italic">Tanpa modifier</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer Actions -->
                    <div class="p-4 pt-2 border-t border-gray-100 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <button
                                wire:click="openEditProductModal({{ $product->id }})"
                                class="text-indigo-600 hover:text-indigo-800 font-semibold flex items-center gap-1 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                                Edit
                            </button>
                            <span class="text-gray-300">|</span>
                            <button
                                wire:click="deleteProduct({{ $product->id }})"
                                wire:confirm="Yakin ingin menghapus produk '{{ $product->name }}'?"
                                class="text-rose-600 hover:text-rose-800 font-medium flex items-center gap-1 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Hapus
                            </button>
                        </div>
                        <span class="text-[11px] text-gray-400">Urutan: #{{ $product->sort_order }}</span>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white p-12 text-center rounded-2xl border border-gray-100 shadow-sm">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">Belum ada menu produk</h3>
                    <p class="mt-1 text-xs text-gray-500">Mulai dengan menambahkan kategori atau klik 'Tambah Produk Baru'.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div>
            {{ $products->links() }}
        </div>

        <!-- Product Modal (Create / Edit) -->
        @if ($showProductModal)
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div wire:click="$set('showProductModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                    <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100">
                        <form wire:submit="saveProduct">
                            <div class="bg-white p-6 space-y-4">
                                <div class="flex items-center justify-between border-b pb-3">
                                    <h3 class="text-lg font-bold text-gray-900">
                                        {{ $editingProductId ? 'Edit Produk' : 'Tambah Produk Baru' }}
                                    </h3>
                                    <button type="button" wire:click="$set('showProductModal', false)" class="text-gray-400 hover:text-gray-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Name -->
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Nama Produk *</label>
                                        <input wire:model="name" type="text" placeholder="Contoh: Kopi Susu Gula Aren" class="w-full text-sm border-gray-200 rounded-xl focus:ring-amber-500 focus:border-amber-500" required />
                                        @error('name') <span class="text-xs text-rose-600 mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Category -->
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Kategori *</label>
                                        <select wire:model="category_id" class="w-full text-sm border-gray-200 rounded-xl focus:ring-amber-500 focus:border-amber-500" required>
                                            <option value="">Pilih Kategori</option>
                                            @foreach ($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('category_id') <span class="text-xs text-rose-600 mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Price -->
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Harga (Rupiah) *</label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs text-gray-500 font-semibold">Rp</span>
                                            <input wire:model="price" type="number" step="1000" min="0" placeholder="25000" class="w-full text-sm pl-9 border-gray-200 rounded-xl focus:ring-amber-500 focus:border-amber-500" required />
                                        </div>
                                        @error('price') <span class="text-xs text-rose-600 mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Description -->
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Deskripsi Singkat</label>
                                        <textarea wire:model="description" rows="2" placeholder="Jelaskan cita rasa atau komposisi produk..." class="w-full text-sm border-gray-200 rounded-xl focus:ring-amber-500 focus:border-amber-500"></textarea>
                                        @error('description') <span class="text-xs text-rose-600 mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Image Upload -->
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Foto Produk (Max 2MB)</label>
                                        <input wire:model="image" type="file" accept="image/*" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100" />
                                        <div wire:loading wire:target="image" class="text-xs text-amber-600 mt-1">Mengunggah foto...</div>
                                        @error('image') <span class="text-xs text-rose-600 mt-1">{{ $message }}</span> @enderror

                                        <!-- Preview -->
                                        @if ($image)
                                            <div class="mt-2 flex items-center gap-2">
                                                <span class="text-xs text-gray-500">Preview baru:</span>
                                                @php
                                                    $canPreview = false;
                                                    $previewUrl = null;
                                                    try {
                                                        if (is_object($image) && method_exists($image, 'isPreviewable') && $image->isPreviewable()) {
                                                            $previewUrl = $image->temporaryUrl();
                                                            $canPreview = true;
                                                        }
                                                    } catch (\Throwable $e) {
                                                        $canPreview = false;
                                                    }
                                                @endphp

                                                @if ($canPreview && $previewUrl)
                                                    <img src="{{ $previewUrl }}" class="h-16 w-16 object-cover rounded-xl border">
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        Foto siap diunggah: {{ method_exists($image, 'getClientOriginalName') ? $image->getClientOriginalName() : 'File Terpilih' }}
                                                    </span>
                                                @endif
                                                <button type="button" wire:click="$set('image', null)" class="text-xs text-rose-500 hover:text-rose-700 underline ms-2">Hapus</button>
                                            </div>
                                        @elseif ($existingImage)
                                            <div class="mt-2 flex items-center gap-2">
                                                <span class="text-xs text-gray-500">Foto saat ini:</span>
                                                <img src="{{ Storage::url($existingImage) }}" class="h-16 w-16 object-cover rounded-xl border">
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Sort Order & Availability -->
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Urutan Tampil</label>
                                        <input wire:model="sort_order" type="number" min="0" class="w-full text-sm border-gray-200 rounded-xl focus:ring-amber-500 focus:border-amber-500" />
                                        @error('sort_order') <span class="text-xs text-rose-600 mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="flex items-center pt-6">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input wire:model="is_available" type="checkbox" class="rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500">
                                            <span class="ms-2 text-sm font-medium text-gray-700">Tersedia untuk Dipesan (Stok Ada)</span>
                                        </label>
                                    </div>

                                    <!-- Modifier Groups Checkboxes -->
                                    <div class="md:col-span-2 pt-2 border-t">
                                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Grup Modifier yang Berlaku</label>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 bg-gray-50 p-3 rounded-xl border border-gray-100">
                                            @foreach ($modifierGroups as $group)
                                                <label wire:key="mg-check-{{ $group->id }}" class="flex items-start gap-2 p-1.5 rounded-lg hover:bg-white transition cursor-pointer">
                                                    <input
                                                        type="checkbox"
                                                        wire:model="selectedModifierGroups"
                                                        value="{{ $group->id }}"
                                                        class="rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500 mt-0.5">
                                                    <div>
                                                        <div class="text-xs font-semibold text-gray-900">{{ $group->name }}</div>
                                                        <div class="text-[11px] text-gray-500">
                                                            {{ $group->is_required ? 'Wajib' : 'Opsional' }} (Pilih {{ $group->min_select }} - {{ $group->max_select }})
                                                        </div>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-6 py-3 flex items-center justify-end gap-2 border-t">
                                <button type="button" wire:click="$set('showProductModal', false)" class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                                    Batal
                                </button>
                                <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-semibold transition shadow-sm">
                                    Simpan Produk
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <!-- Category Management Modal -->
        @if ($showCategoryModal)
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div wire:click="$set('showCategoryModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                    <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                        <div class="p-6 space-y-4">
                            <div class="flex items-center justify-between border-b pb-3">
                                <h3 class="text-lg font-bold text-gray-900">Kelola Kategori Menu</h3>
                                <button type="button" wire:click="$set('showCategoryModal', false)" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- List Categories -->
                            <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                                @foreach ($categories as $cat)
                                    <div wire:key="cat-list-{{ $cat->id }}" class="flex items-center justify-between p-2.5 bg-gray-50 rounded-xl border border-gray-100 text-sm">
                                        <div>
                                            <div class="font-bold text-gray-900">{{ $cat->name }}</div>
                                            <div class="text-[11px] text-gray-500">Urutan: #{{ $cat->sort_order }} | {{ $cat->products_count }} produk</div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button wire:click="openEditCategory({{ $cat->id }})" class="text-xs text-indigo-600 font-semibold hover:underline">Edit</button>
                                            <button wire:click="deleteCategory({{ $cat->id }})" wire:confirm="Hapus kategori '{{ $cat->name }}'?" class="text-xs text-rose-600 font-semibold hover:underline">Hapus</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Form Add / Edit Category -->
                            <form wire:submit="saveCategory" class="pt-4 border-t space-y-3">
                                <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    {{ $editingCategoryId ? 'Edit Kategori Terpilih' : 'Tambah Kategori Baru' }}
                                </h4>

                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Kategori *</label>
                                    <input wire:model="categoryName" type="text" placeholder="Contoh: Signature Drink" class="w-full text-sm border-gray-200 rounded-xl focus:ring-amber-500 focus:border-amber-500" required />
                                    @error('categoryName') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Urutan *</label>
                                        <input wire:model="categorySortOrder" type="number" min="0" class="w-full text-sm border-gray-200 rounded-xl focus:ring-amber-500 focus:border-amber-500" required />
                                    </div>
                                    <div class="flex items-center pt-5">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input wire:model="categoryIsActive" type="checkbox" class="rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500">
                                            <span class="ms-2 text-xs font-medium text-gray-700">Aktif</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end gap-2 pt-2">
                                    @if ($editingCategoryId)
                                        <button type="button" wire:click="openCategoryModal" class="px-3 py-1.5 border rounded-xl text-xs font-medium text-gray-600 hover:bg-gray-50">
                                            Batal Edit
                                        </button>
                                    @endif
                                    <button type="submit" class="px-4 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-semibold shadow-sm">
                                        {{ $editingCategoryId ? 'Simpan Perubahan' : 'Tambah Kategori' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
