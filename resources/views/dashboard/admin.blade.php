<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard Admin
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-gray-900">Selamat datang, <strong>{{ $user->name }}</strong></p>
                <p class="text-gray-500 text-sm mt-1">Role: Administrator</p>
            </div>
        </div>
    </div>
</x-app-layout>