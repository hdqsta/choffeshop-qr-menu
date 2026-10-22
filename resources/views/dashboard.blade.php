<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Selamat Datang, {{ auth()->user()->name }}!</h3>
                            <p class="text-sm text-gray-600 mt-1">Anda login sebagai: <span class="font-semibold text-indigo-600">{{ auth()->user()->roleLabel() }}</span></p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Status Akun: Aktif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
