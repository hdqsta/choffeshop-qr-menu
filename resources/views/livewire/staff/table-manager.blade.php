<div class="py-8 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Top Header & Actions -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14v6m-3-3h6M6 10h2a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2zm10 0h2a2 2 0 002-2V6a2 2 0 00-2-2h-2a2 2 0 00-2 2v2a2 2 0 002 2zM6 20h2a2 2 0 002-2v-2a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2z" />
                    </svg>
                    Manajemen Meja & QR Code
                </h1>
                <p class="text-sm text-gray-500 mt-1">Kelola nomor meja, perbarui token QR pengaman, dan cetak QR Code meja untuk ditempel di meja kafe.</p>
            </div>
            <div>
                <button wire:click="openCreateTableModal" class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-semibold transition shadow-sm">
                    <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Meja Baru
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

        <!-- Tables Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @forelse ($tables as $table)
                @php
                    $tableUrl = url('/t/' . $table->token);
                    $qrImage = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($tableUrl);
                @endphp
                <div wire:key="table-card-{{ $table->id }}" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <!-- Header -->
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200/50">
                                Meja #{{ $table->number }}
                            </span>
                            <button
                                wire:click="toggleTableStatus({{ $table->id }})"
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold transition {{ $table->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                {{ $table->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </div>

                        <!-- QR Code Center Display -->
                        <div class="mt-4 flex flex-col items-center justify-center p-3 bg-gray-50 rounded-xl border border-dashed border-gray-200 cursor-pointer hover:bg-gray-100/70 transition" wire:click="openQrPreview({{ $table->id }})">
                            <img src="{{ $qrImage }}" alt="QR Meja {{ $table->number }}" class="w-32 h-32 rounded-lg bg-white p-1.5 shadow-sm" loading="lazy">
                            <span class="text-[11px] font-medium text-amber-600 hover:underline mt-2 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Klik untuk Perbesar & Cetak
                            </span>
                        </div>

                        <!-- Info & URL -->
                        <div class="mt-3 space-y-1">
                            <div class="text-[11px] text-gray-400 truncate font-mono">
                                Token: {{ Str::limit($table->token, 18) }}
                            </div>
                            <div class="text-[11px] text-gray-500">
                                Total Pesanan: <span class="font-semibold text-gray-700">{{ $table->orders_count }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                        <button
                            wire:click="regenerateToken({{ $table->id }})"
                            wire:confirm="Buat token QR baru untuk Meja {{ $table->number }}? QR lama tidak akan berfungsi lagi."
                            title="Regenerate Token QR jika bocor"
                            class="text-amber-600 hover:underline flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Reset QR
                        </button>
                        <div class="flex items-center gap-2">
                            <button wire:click="openEditTableModal({{ $table->id }})" class="text-indigo-600 hover:underline font-semibold">Edit</button>
                            <span class="text-gray-300">|</span>
                            <button wire:click="deleteTable({{ $table->id }})" wire:confirm="Hapus Meja {{ $table->number }}?" class="text-rose-600 hover:underline font-semibold">Hapus</button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white p-12 text-center rounded-2xl border border-gray-100 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-900">Belum ada meja yang terdaftar</h3>
                    <p class="mt-1 text-xs text-gray-500">Tambahkan nomor meja untuk membuat QR Code meja pelanggan.</p>
                </div>
            @endforelse
        </div>

        <!-- Table Modal -->
        @if ($showTableModal)
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div wire:click="$set('showTableModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                    <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-100">
                        <form wire:submit="saveTable">
                            <div class="p-6 space-y-4">
                                <div class="flex items-center justify-between border-b pb-3">
                                    <h3 class="text-lg font-bold text-gray-900">
                                        {{ $editingTableId ? 'Edit Meja' : 'Tambah Meja Baru' }}
                                    </h3>
                                    <button type="button" wire:click="$set('showTableModal', false)" class="text-gray-400 hover:text-gray-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Nomor / Identitas Meja *</label>
                                    <input wire:model="number" type="text" placeholder="Contoh: 01 atau VIP-1" class="w-full text-sm border-gray-200 rounded-xl focus:ring-amber-500 focus:border-amber-500" required />
                                    @error('number') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                                </div>

                                <div class="pt-2">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input wire:model="isActive" type="checkbox" class="rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500">
                                        <span class="ms-2 text-xs font-semibold text-gray-700">Meja Aktif (Bisa Menerima Pesanan)</span>
                                    </label>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-6 py-3 flex items-center justify-end gap-2 border-t">
                                <button type="button" wire:click="$set('showTableModal', false)" class="px-4 py-2 border rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    Batal
                                </button>
                                <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-semibold shadow-sm">
                                    Simpan Meja
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <!-- QR Code Print Preview Modal -->
        @if ($showQrModal && $previewTable)
            @php
                $fullUrl = url('/t/' . $previewTable->token);
                $bigQr = 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=' . urlencode($fullUrl);
            @endphp
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div wire:click="$set('showQrModal', false)" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                    <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full border border-gray-100">
                        <!-- Tent Card Printable Preview -->
                        <div id="printable-qr-card" class="p-8 text-center bg-white flex flex-col items-center space-y-4">
                            <div class="w-12 h-12 bg-amber-600 text-white rounded-2xl flex items-center justify-center shadow-md">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-xl font-extrabold text-gray-900 tracking-tight">KEDAI KOPI KITA</h2>
                                <p class="text-xs text-gray-500 uppercase tracking-widest mt-0.5">Scan Untuk Pesan & Bayar</p>
                            </div>

                            <!-- Big QR -->
                            <div class="p-3 bg-white rounded-2xl border-2 border-gray-900 shadow-sm">
                                <img src="{{ $bigQr }}" alt="QR Code Meja {{ $previewTable->number }}" class="w-52 h-52">
                            </div>

                            <div class="space-y-1">
                                <span class="text-xs uppercase tracking-wider text-gray-400 font-semibold">Nomor Meja</span>
                                <div class="text-3xl font-black text-gray-900 tracking-tight">
                                    MEJA {{ $previewTable->number }}
                                </div>
                                <p class="text-[11px] text-gray-500 max-w-xs">Buka kamera HP atau aplikasi QRIS, scan kode di atas, pilih menu & bayar langsung dari tempat duduk Anda.</p>
                            </div>
                        </div>

                        <!-- Modal Actions -->
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-between border-t text-xs">
                            <button type="button" wire:click="$set('showQrModal', false)" class="text-gray-500 hover:text-gray-700 font-medium">
                                Tutup
                            </button>
                            <div class="flex items-center gap-2">
                                <a href="{{ $bigQr }}" download="QR_Meja_{{ $previewTable->number }}.png" target="_blank" class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-xl font-medium transition">
                                    Download PNG
                                </a>
                                <button type="button" onclick="window.print()" class="px-4 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-semibold shadow-sm transition">
                                    Cetak Tent Card
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
