<x-simans-layout title="Buka Sesi Absensi">
    <div class="max-w-lg mx-auto">
        <div class="mb-6">
            <a href="{{ route('guru.attendance.index') }}" class="text-gray-500 hover:text-blue-600 text-sm flex items-center gap-1 mb-4 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                Kembali
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Buka Sesi Absensi</h1>
            <p class="text-gray-500 text-sm mt-1">QR Code akan dibuat otomatis setelah sesi dibuka</p>
        </div>

        @if(session('error'))
            <div class="mb-4 bg-red-900/30 border border-red-700/40 text-red-300 px-4 py-3 rounded-xl text-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white border border-gray-200 rounded-xl p-6">
            <form method="POST" action="{{ route('guru.attendance.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1.5">Kelas</label>
                    <select name="classroom_id" required
                            class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                        <option value="">Pilih kelas...</option>
                        @foreach($classrooms as $classroom)
                            <option value="{{ $classroom->id }}" {{ old('classroom_id') == $classroom->id ? 'selected' : '' }}>
                                {{ $classroom->name }} — {{ $classroom->major->code }}
                            </option>
                        @endforeach
                    </select>
                    @error('classroom_id')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1.5">Mata Pelajaran</label>
                    <select name="subject_id" required
                            class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                        <option value="">Pilih mata pelajaran...</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('subject_id')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1.5">Durasi QR Aktif</label>
                    <select name="duration_minutes" required
                            class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                        <option value="5">5 menit</option>
                        <option value="10" selected>10 menit</option>
                        <option value="15">15 menit</option>
                        <option value="30">30 menit</option>
                    </select>
                    <p class="text-gray-500 text-xs mt-1.5">QR Code akan kedaluwarsa setelah durasi ini. Bisa diperbarui kapan saja.</p>
                </div>

                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition-colors text-sm">
                    Buka Sesi & Generate QR Code
                </button>
            </form>
        </div>
    </div>
</x-simans-layout>