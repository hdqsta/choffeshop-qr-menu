<div class="flex-1 flex flex-col">

    <!-- Search Bar -->
    <div class="p-4 bg-white border-b border-stone-100">
        <div class="relative">
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Cari kopi, pastry, atau minuman..."
                class="w-full text-sm pl-10 pr-4 py-2.5 bg-stone-100/80 border-0 rounded-2xl focus:bg-white focus:ring-2 focus:ring-amber-500 placeholder-stone-400 text-stone-900 transition" />
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            @if ($search)
                <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-stone-400 hover:text-stone-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            @endif
        </div>
    </div>

    <!-- Category Tabs (Horizontal Scrollable) -->
    <div class="sticky top-[57px] z-20 bg-white/95 backdrop-blur-md border-b border-stone-100 px-4 py-2.5 overflow-x-auto no-scrollbar flex items-center gap-2">
        <button
            wire:click="selectCategory(null)"
            class="px-4 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-all shadow-xs {{ is_null($activeCategoryId) ? 'bg-amber-700 text-white shadow-amber-900/10' : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }}">
            Semua
        </button>
        @foreach ($categories as $category)
            <button
                wire:key="cat-tab-{{ $category->id }}"
                wire:click="selectCategory({{ $category->id }})"
                class="px-4 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-all shadow-xs {{ $activeCategoryId === $category->id ? 'bg-amber-700 text-white shadow-amber-900/10' : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }}">
                {{ $category->name }}
            </button>
        @endforeach
    </div>

    <!-- Menu Sections -->
    <div class="p-4 space-y-6">
        @forelse ($categories as $category)
            @if (is_null($activeCategoryId) || $activeCategoryId === $category->id)
                @if ($category->products->isNotEmpty())
                    <section wire:key="section-cat-{{ $category->id }}" class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h2 class="text-base font-black text-stone-900 tracking-tight">{{ $category->name }}</h2>
                            <span class="text-xs text-stone-400 font-medium">{{ $category->products->count() }} menu</span>
                        </div>

                        <div class="space-y-3">
                            @foreach ($category->products as $product)
                                <div
                                    wire:key="menu-prod-{{ $product->id }}"
                                    wire:click="openProductModal({{ $product->id }})"
                                    class="p-3.5 bg-white rounded-2xl border border-stone-100 shadow-xs hover:border-amber-200 transition-all flex gap-3.5 items-center cursor-pointer active:scale-[0.99] {{ ! $product->is_available ? 'opacity-60 bg-stone-50' : '' }}">

                                    <!-- Product Info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-2">
                                            <h3 class="text-sm font-bold text-stone-900 leading-snug line-clamp-1">{{ $product->name }}</h3>
                                        </div>

                                        @if ($product->description)
                                            <p class="text-xs text-stone-500 line-clamp-2 mt-1 leading-relaxed">{{ $product->description }}</p>
                                        @endif

                                        <div class="mt-2.5 flex items-center justify-between">
                                            <span class="text-sm font-extrabold text-amber-700">{{ $product->formatted_price }}</span>

                                            @if ($product->is_available)
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-600 text-white shadow-xs hover:bg-amber-700 active:scale-95 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                                    </svg>
                                                </button>
                                            @else
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-stone-200 text-stone-600">
                                                    Habis
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Thumbnail -->
                                    <div class="w-20 h-20 shrink-0 rounded-2xl overflow-hidden bg-amber-50 border border-stone-100 relative">
                                        @if ($product->image)
                                            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-amber-300">
                                                <span class="text-2xl">☕</span>
                                            </div>
                                        @endif

                                        @if (! $product->is_available)
                                            <div class="absolute inset-0 bg-black/40 backdrop-blur-[1px] flex items-center justify-center">
                                                <span class="text-[10px] font-extrabold text-white tracking-wider uppercase bg-red-600/90 px-1.5 py-0.5 rounded">Habis</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endif
        @empty
            <div class="text-center py-12">
                <span class="text-4xl">🔍</span>
                <p class="text-sm font-bold text-stone-800 mt-2">Menu tidak ditemukan</p>
                <p class="text-xs text-stone-500 mt-1">Coba kata kunci lain atau pilih kategori yang tersedia.</p>
            </div>
        @endforelse
    </div>

    <!-- Product Customization Modal (Bottom Sheet on Mobile) -->
    @if ($showModal && $selectedProduct)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end sm:items-center justify-center min-h-screen text-center p-0 sm:p-4">
                <!-- Overlay Backdrop -->
                <div wire:click="closeModal" class="fixed inset-0 bg-stone-900/60 backdrop-blur-xs transition-opacity"></div>

                <!-- Sheet Modal Container -->
                <div class="relative w-full max-w-md bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl text-left overflow-hidden transform transition-all max-h-[90vh] flex flex-col">

                    <!-- Modal Header with Image -->
                    <div class="relative">
                        <div class="h-44 w-full bg-amber-100 overflow-hidden relative">
                            @if ($selectedProduct->image)
                                <img src="{{ Storage::url($selectedProduct->image) }}" alt="{{ $selectedProduct->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-amber-100 to-amber-200 text-amber-700">
                                    <span class="text-4xl">☕</span>
                                </div>
                            @endif
                        </div>

                        <!-- Close Button -->
                        <button
                            type="button"
                            wire:click="closeModal"
                            class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 backdrop-blur-md shadow-md text-stone-700 flex items-center justify-center hover:bg-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Scrollable Options Body -->
                    <div class="p-5 overflow-y-auto flex-1 space-y-5">
                        <div>
                            <h3 class="text-lg font-black text-stone-900">{{ $selectedProduct->name }}</h3>
                            <p class="text-xs text-stone-500 mt-1 leading-relaxed">{{ $selectedProduct->description }}</p>
                            <div class="text-base font-extrabold text-amber-700 mt-2">{{ $selectedProduct->formatted_price }}</div>
                        </div>

                        <!-- Modifier Groups -->
                        @foreach ($selectedProduct->modifierGroups as $group)
                            <div wire:key="cust-mg-{{ $group->id }}" class="pt-4 border-t border-stone-100 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-extrabold text-stone-800 uppercase tracking-wider">
                                        {{ $group->name }}
                                        @if ($group->is_required)
                                            <span class="text-rose-500">*</span>
                                        @endif
                                    </label>
                                    <span class="text-[11px] font-semibold text-stone-400">
                                        {{ $group->is_required ? 'Wajib' : 'Opsional' }} (Pilih {{ $group->min_select }} - {{ $group->max_select }})
                                    </span>
                                </div>

                                @error("modifiers.{$group->id}")
                                    <p class="text-xs text-rose-500 font-semibold">{{ $message }}</p>
                                @enderror

                                <!-- Options Selection -->
                                <div class="space-y-2">
                                    @foreach ($group->options as $option)
                                        @php
                                            $isSingle = $group->max_select === 1;
                                            $isChecked = $isSingle
                                                ? (($selectedModifiers[$group->id] ?? null) == $option->id)
                                                : (in_array($option->id, (array) ($selectedModifiers[$group->id] ?? [])));
                                        @endphp
                                        <label
                                            wire:key="cust-opt-{{ $option->id }}"
                                            class="flex items-center justify-between p-3 rounded-2xl border transition-all cursor-pointer {{ $isChecked ? 'border-amber-600 bg-amber-50/50' : 'border-stone-200 hover:border-stone-300' }}">

                                            <div class="flex items-center gap-3">
                                                @if ($isSingle)
                                                    <input
                                                        type="radio"
                                                        name="group_{{ $group->id }}"
                                                        wire:model.live="selectedModifiers.{{ $group->id }}"
                                                        value="{{ $option->id }}"
                                                        class="w-4 h-4 text-amber-600 border-stone-300 focus:ring-amber-500">
                                                @else
                                                    <input
                                                        type="checkbox"
                                                        wire:model.live="selectedModifiers.{{ $group->id }}"
                                                        value="{{ $option->id }}"
                                                        class="w-4 h-4 rounded text-amber-600 border-stone-300 focus:ring-amber-500">
                                                @endif
                                                <span class="text-xs font-semibold text-stone-800">{{ $option->name }}</span>
                                            </div>

                                            <span class="text-xs font-bold {{ $option->price_delta > 0 ? 'text-amber-700' : 'text-stone-400' }}">
                                                {{ $option->formatted_price_delta }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <!-- Special Notes -->
                        <div class="pt-4 border-t border-stone-100">
                            <label class="block text-xs font-extrabold text-stone-800 uppercase tracking-wider mb-1.5">
                                Catatan Khusus (Opsional)
                            </label>
                            <input
                                wire:model="notes"
                                type="text"
                                placeholder="Contoh: jangan terlalu manis, es sedikit..."
                                class="w-full text-xs p-3 bg-stone-50 border border-stone-200 rounded-2xl focus:bg-white focus:ring-amber-500 focus:border-amber-500 text-stone-800 placeholder-stone-400" />
                        </div>
                    </div>

                    <!-- Modal Sticky Footer (Quantity + Add Button) -->
                    <div class="p-4 bg-white border-t border-stone-100 flex items-center gap-3">
                        <!-- Quantity Counter -->
                        <div class="flex items-center bg-stone-100 rounded-2xl p-1 shrink-0">
                            <button
                                type="button"
                                wire:click="decrementQuantity"
                                class="w-8 h-8 rounded-xl bg-white shadow-xs flex items-center justify-center font-bold text-stone-700 active:scale-90 transition">
                                -
                            </button>
                            <span class="w-8 text-center text-xs font-black text-stone-900">{{ $quantity }}</span>
                            <button
                                type="button"
                                wire:click="incrementQuantity"
                                class="w-8 h-8 rounded-xl bg-white shadow-xs flex items-center justify-center font-bold text-stone-700 active:scale-90 transition">
                                +
                            </button>
                        </div>

                        <!-- Add to Cart Button -->
                        <button
                            type="button"
                            wire:click="addToCart"
                            class="flex-1 py-3 px-4 bg-amber-600 hover:bg-amber-700 active:scale-[0.98] text-white rounded-2xl text-xs font-bold transition shadow-md shadow-amber-900/10 flex items-center justify-between">
                            <span>Tambah ke Pesanan</span>
                            <span>Rp {{ number_format($this->modal_total_price, 0, ',', '.') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Floating Bottom Cart Bar -->
    @if ($cartCount > 0)
        <div class="fixed bottom-3 inset-x-0 z-40 max-w-md mx-auto px-4">
            <a
                href="{{ route('customer.cart') }}"
                wire:navigate
                class="w-full bg-gradient-to-r from-stone-900 to-stone-800 text-white p-3.5 rounded-2xl shadow-xl shadow-stone-900/20 flex items-center justify-between border border-stone-700/50 active:scale-[0.99] transition">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-amber-600 text-white flex items-center justify-center text-xs font-black">
                        {{ $cartCount }}
                    </div>
                    <div>
                        <div class="text-[11px] text-stone-300 font-medium">{{ $cartCount }} Item di Keranjang</div>
                        <div class="text-sm font-extrabold text-white">Rp {{ number_format($cartTotal, 0, ',', '.') }}</div>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 text-xs font-bold text-amber-400">
                    <span>Lihat Pesanan</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>
        </div>
    @endif

</div>
