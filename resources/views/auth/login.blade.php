<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="login" value="Email / Username / NIS" />
            <x-text-input
                id="login"
                name="login"
                type="text"
                class="mt-1 block w-full"
                :value="old('login')"
                required
                autofocus
                autocomplete="username"
                placeholder="Masukkan email, username, atau NIS"
            />
            <x-input-error :messages="$errors->get('login')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Password" />
            <x-text-input
                id="password"
                name="password"
                type="password"
                class="mt-1 block w-full"
                required
                autocomplete="current-password"
                placeholder="Masukkan password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input
                    id="remember_me"
                    type="checkbox"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                    name="remember"
                />
                <span class="ms-2 text-sm text-gray-600">Ingat saya</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Masuk
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>