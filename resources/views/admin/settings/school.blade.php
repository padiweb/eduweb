<x-simans-layout title="Pengaturan Sekolah">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Pengaturan Sekolah</h1>
        <p class="text-gray-400 text-sm mt-1">Kelola informasi, jam absensi, lokasi, dan aturan pelanggaran</p>
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

            {{-- Kolom Kiri: Info + Jam + Pelanggaran --}}
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
                                <option value="Asia/Jakarta"  {{ old('timezone', $school->timezone ?? 'Asia/Jakarta') === 'Asia/Jakarta'  ? 'selected' : '' }}>WIB — Waktu Indonesia Barat (UTC+7)</option>
                                <option value="Asia/Makassar" {{ old('timezone', $school->timezone ?? 'Asia/Jakarta') === 'Asia/Makassar' ? 'selected' : '' }}>WITA — Waktu Indonesia Tengah (UTC+8)</option>
                                <option value="Asia/Jayapura" {{ old('timezone', $school->timezone ?? 'Asia/Jakarta') === 'Asia/Jayapura' ? 'selected' : '' }}>WIT — Waktu Indonesia Timur (UTC+9)</option>
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

                {{-- Pengaturan Jam Absensi --}}
                <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-white mb-1 flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Jam Absensi Siswa
                    </h2>
                    <p class="text-gray-500 text-xs mb-4">Siswa hanya bisa scan QR dalam rentang jam buka hingga jam tutup.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Jam Buka <span class="text-red-400">*</span></label>
                            <input type="time" name="school_start_time"
                                   value="{{ old('school_start_time', substr($school->school_start_time, 0, 5)) }}"
                                   class="w-full bg-gray-800 border {{ $errors->has('school_start_time') ? 'border-red-500' : 'border-white/10' }} text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            @error('school_start_time') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Batas Terlambat <span class="text-red-400">*</span></label>
                            <input type="time" name="late_threshold_time"
                                   value="{{ old('late_threshold_time', substr($school->late_threshold_time, 0, 5)) }}"
                                   class="w-full bg-gray-800 border {{ $errors->has('late_threshold_time') ? 'border-red-500' : 'border-white/10' }} text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            @error('late_threshold_time') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Jam Tutup <span class="text-red-400">*</span></label>
                            <input type="time" name="attendance_close_time"
                                   value="{{ old('attendance_close_time', substr($school->attendance_close_time, 0, 5)) }}"
                                   class="w-full bg-gray-800 border {{ $errors->has('attendance_close_time') ? 'border-red-500' : 'border-white/10' }} text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            @error('attendance_close_time') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- ── Pengaturan Pelanggaran & Peringatan ── --}}
                <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-white mb-1 flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                        Pelanggaran &amp; Peringatan
                    </h2>
                    <p class="text-gray-500 text-xs mb-4">
                        Atur batas poin untuk setiap level peringatan. Poin berlaku selama siswa aktif (tidak reset per semester).
                    </p>

                    {{-- Batas poin peringatan --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">
                                Batas Peringatan 1 <span class="text-red-400">*</span>
                            </label>
                            <input type="number" name="violation_warning1" min="1" max="999"
                                   value="{{ old('violation_warning1', $school->violation_warning1 ?? 10) }}"
                                   class="w-full bg-gray-800 border {{ $errors->has('violation_warning1') ? 'border-red-500' : 'border-amber-500/40' }} text-white rounded-xl px-4 py-2.5 text-sm font-semibold focus:outline-none focus:border-amber-500 transition-colors">
                            <p class="text-xs text-amber-600 mt-1">poin · Peringatan 1</p>
                            @error('violation_warning1') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">
                                Batas Peringatan 2 <span class="text-red-400">*</span>
                            </label>
                            <input type="number" name="violation_warning2" min="1" max="999"
                                   value="{{ old('violation_warning2', $school->violation_warning2 ?? 20) }}"
                                   class="w-full bg-gray-800 border {{ $errors->has('violation_warning2') ? 'border-red-500' : 'border-orange-500/40' }} text-white rounded-xl px-4 py-2.5 text-sm font-semibold focus:outline-none focus:border-orange-500 transition-colors">
                            <p class="text-xs text-orange-600 mt-1">poin · Peringatan 2</p>
                            @error('violation_warning2') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">
                                Batas Peringatan 3 <span class="text-red-400">*</span>
                            </label>
                            <input type="number" name="violation_warning3" min="1" max="999"
                                   value="{{ old('violation_warning3', $school->violation_warning3 ?? 30) }}"
                                   class="w-full bg-gray-800 border {{ $errors->has('violation_warning3') ? 'border-red-500' : 'border-red-500/40' }} text-white rounded-xl px-4 py-2.5 text-sm font-semibold focus:outline-none focus:border-red-500 transition-colors">
                            <p class="text-xs text-red-600 mt-1">poin · Peringatan 3</p>
                            @error('violation_warning3') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Batas alfa per semester --}}
                    <div class="border-t border-white/5 pt-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-400 mb-1.5">
                                    Batas Alfa per Semester
                                </label>
                                <input type="number" name="alfa_limit_per_semester" min="0" max="999"
                                       value="{{ old('alfa_limit_per_semester', $school->alfa_limit_per_semester ?? 0) }}"
                                       class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                                <p class="text-gray-600 text-xs mt-1">
                                    hari · Isi 0 untuk menonaktifkan batas
                                </p>
                                @error('alfa_limit_per_semester') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="bg-gray-800 border border-white/10 rounded-xl p-3 text-xs text-gray-400">
                                <p class="font-semibold text-white mb-1">Cara kerja alfa:</p>
                                <p>Poin dari alfa berlaku selama siswa sekolah ({{ $school->school_program_years ?? 3 }} tahun), tidak reset per semester.</p>
                                <p class="mt-1">Batas alfa per semester hanya untuk monitoring kesiswaan, tidak otomatis memblokir siswa.</p>
                            </div>
                        </div>
                    </div>
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

            {{-- Kolom Kanan: GPS --}}
            <div class="space-y-5">
                <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-white mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                        </svg>
                        Lokasi GPS Sekolah
                    </h2>

                    @if($school->latitude && $school->longitude)
                        <div class="flex items-center gap-2 mb-3 text-xs text-emerald-400">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            GPS sudah diatur
                        </div>
                    @else
                        <div class="flex items-center gap-2 mb-3 text-xs text-red-400">
                            <span class="w-2 h-2 rounded-full bg-red-400"></span>
                            GPS belum diatur
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
            </div>
        </div>

        {{-- Jam Absensi Guru --}}
        <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
            <h2 class="text-sm font-semibold text-white mb-1">Jam Absensi Guru</h2>
            <p class="text-xs text-gray-500 mb-4">Sesi masuk dan pulang dibuat otomatis sesuai jam di bawah.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">Buka Absen Masuk</label>
                    <input type="time" name="teacher_checkin_open"
                           value="{{ substr($school->teacher_checkin_open ?? '06:30', 0, 5) }}"
                           class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">Batas Terlambat Masuk</label>
                    <input type="time" name="teacher_checkin_late"
                           value="{{ substr($school->teacher_checkin_late ?? '07:15', 0, 5) }}"
                           class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">Tutup Absen Masuk</label>
                    <input type="time" name="teacher_checkin_close"
                           value="{{ substr($school->teacher_checkin_close ?? '08:00', 0, 5) }}"
                           class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">Buka Absen Pulang</label>
                    <input type="time" name="teacher_checkout_open"
                           value="{{ substr($school->teacher_checkout_open ?? '14:00', 0, 5) }}"
                           class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1.5">Tutup Absen Pulang</label>
                    <input type="time" name="teacher_checkout_close"
                           value="{{ substr($school->teacher_checkout_close ?? '16:00', 0, 5) }}"
                           class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                </div>
                <div class="flex items-end">
                    <a href="{{ route('admin.teacher-attendance.qr') }}"
                       target="_blank"
                       class="w-full flex items-center justify-center gap-2 bg-gray-800 hover:bg-gray-700 border border-white/10 text-gray-300 text-sm px-4 py-2.5 rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
                        </svg>
                        Cetak QR Guru
                    </a>
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

    <script>
    document.getElementById('btn-detect-location')?.addEventListener('click', function() {
        var statusEl = document.getElementById('detect-status');
        statusEl.textContent = 'Mendeteksi lokasi...';
        this.disabled = true;

        if (!navigator.geolocation) {
            statusEl.textContent = 'Browser tidak mendukung GPS.';
            this.disabled = false;
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function(pos) {
                document.getElementById('input-latitude').value  = pos.coords.latitude.toFixed(8);
                document.getElementById('input-longitude').value = pos.coords.longitude.toFixed(8);
                statusEl.textContent = 'Lokasi terdeteksi! Akurasi: \u00b1' + Math.round(pos.coords.accuracy) + 'm. Klik Simpan untuk menyimpan.';
                statusEl.className   = 'text-xs text-center text-emerald-400';
                document.getElementById('btn-detect-location').disabled = false;
            },
            function(err) {
                var msgs = { 1: 'Izinkan akses lokasi di browser.', 2: 'GPS tidak tersedia.', 3: 'GPS timeout. Coba lagi.' };
                statusEl.textContent = msgs[err.code] || 'Gagal deteksi lokasi.';
                statusEl.className   = 'text-xs text-center text-red-400';
                document.getElementById('btn-detect-location').disabled = false;
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    });
    </script>

</x-simans-layout>