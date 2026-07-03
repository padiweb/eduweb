<x-simans-layout title="Pengaturan Sekolah">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Pengaturan Sekolah</h1>
        <p class="text-gray-400 text-sm mt-1">Kelola informasi, jam absensi, dan lokasi sekolah</p>
    </div>

    @if(session('success'))
        <div class="mb-5 flex items-center gap-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-300 px-4 py-3 rounded-xl text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.school.update') }}">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ── Kolom Kiri: Info + Jam ── --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Informasi Sekolah --}}
                <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342"/>
                        </svg>
                        Informasi Sekolah
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Nama Sekolah <span class="text-red-400">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $school->name) }}"
                                   class="w-full bg-gray-800 border {{ $errors->has('name') ? 'border-red-500' : 'border-white/10' }} text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">NPSN</label>
                            <input type="text" name="npsn" value="{{ old('npsn', $school->npsn) }}"
                                   class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors"
                                   placeholder="Nomor Pokok Sekolah">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">No. Telepon</label>
                            <input type="text" name="phone" value="{{ old('phone', $school->phone) }}"
                                   class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors"
                                   placeholder="0271-xxxxxxx">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Email</label>
                            <input type="email" name="email" value="{{ old('email', $school->email) }}"
                                   class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors"
                                   placeholder="email@sekolah.sch.id">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Program Sekolah</label>
                            <select name="school_program_years"
                                    class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                                <option value="3" {{ old('school_program_years', $school->school_program_years) == 3 ? 'selected' : '' }}>
                                    3 Tahun (6 Semester) — SMK/SMA
                                </option>
                                <option value="4" {{ old('school_program_years', $school->school_program_years) == 4 ? 'selected' : '' }}>
                                    4 Tahun (8 Semester)
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Zona Waktu</label>
                            <select name="timezone"
                                    class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                                <option value="Asia/Jakarta"  {{ old('timezone', $school->timezone ?? 'Asia/Jakarta') === 'Asia/Jakarta'  ? 'selected' : '' }}>
                                    WIB — Waktu Indonesia Barat (UTC+7)
                                </option>
                                <option value="Asia/Makassar" {{ old('timezone', $school->timezone ?? 'Asia/Jakarta') === 'Asia/Makassar' ? 'selected' : '' }}>
                                    WITA — Waktu Indonesia Tengah (UTC+8)
                                </option>
                                <option value="Asia/Jayapura" {{ old('timezone', $school->timezone ?? 'Asia/Jakarta') === 'Asia/Jayapura' ? 'selected' : '' }}>
                                    WIT — Waktu Indonesia Timur (UTC+9)
                                </option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Alamat</label>
                            <textarea name="address" rows="2"
                                      class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors resize-none"
                                      placeholder="Alamat lengkap sekolah">{{ old('address', $school->address) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- ── Pengaturan Jam Absensi ── --}}
                <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-white mb-1 flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Jam Absensi Siswa
                    </h2>
                    <p class="text-gray-500 text-xs mb-4">
                        Siswa hanya bisa scan QR dalam rentang jam buka hingga jam tutup.
                        Scan setelah batas tepat waktu → status Terlambat.
                    </p>

                    {{-- Timeline visual --}}
                    <div class="flex items-center gap-2 mb-5 overflow-x-auto pb-2">
                        <div class="flex-shrink-0 text-center">
                            <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-3 py-2 min-w-[80px]">
                                <p class="text-xs text-emerald-400 font-medium">Buka</p>
                                <p class="text-white font-bold text-sm">{{ substr($school->school_start_time, 0, 5) }}</p>
                            </div>
                        </div>
                        <div class="flex-1 h-0.5 bg-emerald-500/30 min-w-[20px]"></div>
                        <div class="flex-shrink-0 text-center">
                            <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl px-3 py-2 min-w-[80px]">
                                <p class="text-xs text-amber-400 font-medium">Batas</p>
                                <p class="text-white font-bold text-sm">{{ substr($school->late_threshold_time, 0, 5) }}</p>
                            </div>
                        </div>
                        <div class="flex-1 h-0.5 bg-amber-500/30 min-w-[20px]"></div>
                        <div class="flex-shrink-0 text-center">
                            <div class="bg-red-500/10 border border-red-500/30 rounded-xl px-3 py-2 min-w-[80px]">
                                <p class="text-xs text-red-400 font-medium">Tutup</p>
                                <p class="text-white font-bold text-sm">{{ substr($school->attendance_close_time, 0, 5) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">
                                Jam Buka Absensi <span class="text-red-400">*</span>
                            </label>
                            <input type="time" name="school_start_time"
                                   value="{{ old('school_start_time', substr($school->school_start_time, 0, 5)) }}"
                                   class="w-full bg-gray-800 border {{ $errors->has('school_start_time') ? 'border-red-500' : 'border-white/10' }} text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            <p class="text-gray-600 text-xs mt-1">Siswa mulai bisa scan QR</p>
                            @error('school_start_time') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">
                                Batas Tepat Waktu <span class="text-red-400">*</span>
                            </label>
                            <input type="time" name="late_threshold_time"
                                   value="{{ old('late_threshold_time', substr($school->late_threshold_time, 0, 5)) }}"
                                   class="w-full bg-gray-800 border {{ $errors->has('late_threshold_time') ? 'border-red-500' : 'border-amber-500/30' }} text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-500 transition-colors">
                            <p class="text-gray-600 text-xs mt-1">Setelah jam ini → Terlambat</p>
                            @error('late_threshold_time') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">
                                Jam Tutup Absensi <span class="text-red-400">*</span>
                            </label>
                            <input type="time" name="attendance_close_time"
                                   value="{{ old('attendance_close_time', substr($school->attendance_close_time, 0, 5)) }}"
                                   class="w-full bg-gray-800 border {{ $errors->has('attendance_close_time') ? 'border-red-500' : 'border-red-500/20' }} text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-red-500 transition-colors">
                            <p class="text-gray-600 text-xs mt-1">Setelah jam ini → tidak bisa scan</p>
                            @error('attendance_close_time') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Contoh pengaturan --}}
                    <div class="mt-4 bg-gray-800 rounded-xl p-3">
                        <p class="text-xs text-gray-400 font-medium mb-2">Contoh pengaturan umum:</p>
                        <div class="grid grid-cols-3 gap-2 text-xs text-gray-500">
                            <div>
                                <p class="text-gray-300 font-medium">Standar</p>
                                <p>Buka: 06:30</p>
                                <p>Batas: 07:15</p>
                                <p>Tutup: 08:00</p>
                            </div>
                            <div>
                                <p class="text-gray-300 font-medium">Lebih ketat</p>
                                <p>Buka: 06:00</p>
                                <p>Batas: 07:00</p>
                                <p>Tutup: 07:30</p>
                            </div>
                            <div>
                                <p class="text-gray-300 font-medium">Lebih longgar</p>
                                <p>Buka: 07:00</p>
                                <p>Batas: 07:30</p>
                                <p>Tutup: 09:00</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── Kolom Kanan: GPS ── --}}
            <div class="space-y-5">

                {{-- GPS & Radius --}}
                <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-white mb-1 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                        </svg>
                        Lokasi GPS Sekolah
                    </h2>
                    <p class="text-gray-500 text-xs mb-4">
                        Siswa harus berada dalam radius ini untuk bisa absen.
                    </p>

                    {{-- Status GPS saat ini --}}
                    @if($school->latitude && $school->longitude)
                        <div class="flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/20 rounded-xl px-3 py-2 mb-4">
                            <svg class="w-3.5 h-3.5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-emerald-400 text-xs font-medium">GPS sudah diatur</span>
                        </div>
                    @else
                        <div class="flex items-center gap-2 bg-red-500/10 border border-red-500/20 rounded-xl px-3 py-2 mb-4">
                            <svg class="w-3.5 h-3.5 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                            </svg>
                            <span class="text-red-400 text-xs font-medium">GPS belum diatur</span>
                        </div>
                    @endif

                    <div class="space-y-3 mb-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Latitude</label>
                            <input type="number" name="latitude" id="input-latitude" step="0.00000001"
                                   value="{{ old('latitude', $school->latitude) }}"
                                   class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors"
                                   placeholder="-7.324406">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Longitude</label>
                            <input type="number" name="longitude" id="input-longitude" step="0.00000001"
                                   value="{{ old('longitude', $school->longitude) }}"
                                   class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors"
                                   placeholder="110.969947">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">
                                Radius Absensi (meter) <span class="text-red-400">*</span>
                            </label>
                            <input type="number" name="attendance_radius_meters"
                                   value="{{ old('attendance_radius_meters', $school->attendance_radius_meters) }}"
                                   min="50" max="1000"
                                   class="w-full bg-gray-800 border {{ $errors->has('attendance_radius_meters') ? 'border-red-500' : 'border-white/10' }} text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            <p class="text-gray-600 text-xs mt-1">Minimal 50m, maksimal 1000m. Rekomendasi: 200m</p>
                            @error('attendance_radius_meters') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Tombol deteksi lokasi saat ini --}}
                    <button type="button" id="btn-detect-location"
                            class="w-full flex items-center justify-center gap-2 bg-blue-500/10 hover:bg-blue-500/20 border border-blue-500/30 text-blue-400 text-sm font-medium py-2.5 rounded-xl transition-colors mb-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                        </svg>
                        Gunakan Lokasi Saat Ini
                    </button>

                    <p id="detect-status" class="text-xs text-center text-gray-500"></p>

                    <p class="text-gray-600 text-xs mt-3 leading-relaxed">
                        Klik tombol di atas saat berada di area sekolah untuk mengisi koordinat otomatis.
                        Atau isi manual dari Google Maps.
                    </p>
                </div>

                {{-- Info paket --}}
                <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-white mb-3">Paket Aktif</h2>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-bold
                            {{ $school->package === 'enterprise' ? 'bg-purple-500/20 text-purple-400 border border-purple-500/30' :
                               ($school->package === 'pro' ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30' :
                               'bg-gray-700 text-gray-400 border border-white/10') }}">
                            {{ strtoupper($school->package) }}
                        </span>
                        @if($school->active_until)
                            <span class="text-xs text-gray-500">
                                Aktif hingga {{ $school->active_until->translatedFormat('d F Y') }}
                            </span>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        {{-- Tombol simpan --}}
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('admin.dashboard') }}"
               class="px-6 py-2.5 text-sm font-medium rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-300 border border-white/10 transition-colors">
                Batal
            </a>
            <button type="submit"
                    class="px-6 py-2.5 text-sm font-semibold rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white transition-colors">
                Simpan Pengaturan
            </button>
        </div>
    </form>

</x-simans-layout>

@push('scripts')
<script>
document.getElementById('btn-detect-location')?.addEventListener('click', function() {
    const statusEl = document.getElementById('detect-status');
    statusEl.textContent = 'Mendeteksi lokasi...';
    this.disabled = true;

    if (! navigator.geolocation) {
        statusEl.textContent = 'Browser tidak mendukung GPS.';
        this.disabled = false;
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function(pos) {
            document.getElementById('input-latitude').value  = pos.coords.latitude.toFixed(8);
            document.getElementById('input-longitude').value = pos.coords.longitude.toFixed(8);
            statusEl.textContent = 'Lokasi terdeteksi! Akurasi: ±' + Math.round(pos.coords.accuracy) + 'm. Klik Simpan untuk menyimpan.';
            statusEl.className   = 'text-xs text-center text-emerald-400';
            document.getElementById('btn-detect-location').disabled = false;
        },
        function(err) {
            const msgs = {
                1: 'Izinkan akses lokasi di browser.',
                2: 'GPS tidak tersedia.',
                3: 'GPS timeout. Coba lagi.',
            };
            statusEl.textContent = msgs[err.code] || 'Gagal deteksi lokasi.';
            statusEl.className   = 'text-xs text-center text-red-400';
            document.getElementById('btn-detect-location').disabled = false;
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
    );
});
</script>
@endpush
