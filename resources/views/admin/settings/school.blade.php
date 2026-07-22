<x-simans-layout title="Pengaturan Sekolah">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Pengaturan Sekolah</h1>
        <p class="text-gray-500 text-sm mt-1">Kelola informasi, jam absensi, lokasi, dan aturan pelanggaran</p>
    </div>

    @if(session('success'))
        <div class="mb-5 flex items-center gap-3 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.school.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Kolom Kiri: Info + Jam + Pelanggaran --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Informasi Sekolah --}}
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342"/>
                        </svg>
                        Informasi Sekolah
                    </h2>

                    {{-- Upload Logo --}}
                    <div class="mb-5 pb-5 border-b border-gray-200">
                        <label class="block text-xs font-medium text-gray-500 mb-3">Logo Sekolah</label>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-xl bg-white border border-gray-200 flex items-center justify-center overflow-hidden flex-shrink-0" id="logo-preview-wrap">
                                @if($school->logo_path)
                                    <img src="{{ Storage::url($school->logo_path) }}" alt="Logo" id="logo-preview" style="width:64px;height:64px;object-fit:contain">
                                @else
                                    <svg class="w-7 h-7 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" id="logo-placeholder">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342"/>
                                    </svg>
                                    <img src="" alt="" id="logo-preview" style="display:none;width:64px;height:64px;object-fit:contain">
                                @endif
                            </div>
                            <div>
                                <label for="logo-input" class="cursor-pointer inline-flex items-center gap-2 bg-white hover:bg-gray-50 border border-gray-200 text-gray-600 text-xs font-medium px-4 py-2 rounded-lg transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                    </svg>
                                    Pilih Logo
                                </label>
                                <input type="file" name="logo" id="logo-input" accept="image/png,image/jpeg,image/webp" class="hidden"
                                    onchange="
                                        var f=this.files[0];
                                        if(f){
                                            var r=new FileReader();
                                            r.onload=function(e){
                                                var img=document.getElementById('logo-preview');
                                                var ph=document.getElementById('logo-placeholder');
                                                img.src=e.target.result;
                                                img.style.display='block';
                                                if(ph) ph.style.display='none';
                                            };
                                            r.readAsDataURL(f);
                                        }
                                    ">
                                <p class="text-xs text-gray-500 mt-1.5">PNG, JPG, WebP · Maks 2MB</p>
                                <p class="text-xs text-gray-500">Disarankan ukuran minimal 200×200px</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Nama Sekolah <span class="text-red-600">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $school->name) }}"
                                   class="w-full bg-white border {{ $errors->has('name') ? 'border-red-500' : 'border-gray-200' }} text-gray-900 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                            @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">NPSN</label>
                            <input type="text" name="npsn" value="{{ old('npsn', $school->npsn) }}"
                                   class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors"
                                   placeholder="Nomor Pokok Sekolah">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">No. Telepon</label>
                            <input type="text" name="phone" value="{{ old('phone', $school->phone) }}"
                                   class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors"
                                   placeholder="0271-xxxxxxx">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Email</label>
                            <input type="email" name="email" value="{{ old('email', $school->email) }}"
                                   class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors"
                                   placeholder="email@sekolah.sch.id">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Program Sekolah</label>
                            <select name="school_program_years"
                                    class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                                <option value="3" {{ old('school_program_years', $school->school_program_years) == 3 ? 'selected' : '' }}>
                                    3 Tahun (6 Semester) — SMK/SMA
                                </option>
                                <option value="4" {{ old('school_program_years', $school->school_program_years) == 4 ? 'selected' : '' }}>
                                    4 Tahun (8 Semester)
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Zona Waktu</label>
                            <select name="timezone"
                                    class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                                <option value="Asia/Jakarta"  {{ old('timezone', $school->timezone ?? 'Asia/Jakarta') === 'Asia/Jakarta'  ? 'selected' : '' }}>WIB — Waktu Indonesia Barat (UTC+7)</option>
                                <option value="Asia/Makassar" {{ old('timezone', $school->timezone ?? 'Asia/Jakarta') === 'Asia/Makassar' ? 'selected' : '' }}>WITA — Waktu Indonesia Tengah (UTC+8)</option>
                                <option value="Asia/Jayapura" {{ old('timezone', $school->timezone ?? 'Asia/Jakarta') === 'Asia/Jayapura' ? 'selected' : '' }}>WIT — Waktu Indonesia Timur (UTC+9)</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Alamat</label>
                            <textarea name="address" rows="2"
                                      class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors resize-none"
                                      placeholder="Alamat lengkap sekolah">{{ old('address', $school->address) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Pengaturan Jam Absensi --}}
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-1 flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Jam Absensi Siswa
                    </h2>
                    <p class="text-gray-500 text-xs mb-4">Siswa hanya bisa scan QR dalam rentang jam buka hingga jam tutup.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Jam Buka <span class="text-red-600">*</span></label>
                            <input type="time" name="school_start_time"
                                   value="{{ old('school_start_time', substr($school->school_start_time, 0, 5)) }}"
                                   class="w-full bg-white border {{ $errors->has('school_start_time') ? 'border-red-500' : 'border-gray-200' }} text-gray-900 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                            @error('school_start_time') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Batas Terlambat <span class="text-red-600">*</span></label>
                            <input type="time" name="late_threshold_time"
                                   value="{{ old('late_threshold_time', substr($school->late_threshold_time, 0, 5)) }}"
                                   class="w-full bg-white border {{ $errors->has('late_threshold_time') ? 'border-red-500' : 'border-gray-200' }} text-gray-900 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                            @error('late_threshold_time') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Jam Tutup <span class="text-red-600">*</span></label>
                            <input type="time" name="attendance_close_time"
                                   value="{{ old('attendance_close_time', substr($school->attendance_close_time, 0, 5)) }}"
                                   class="w-full bg-white border {{ $errors->has('attendance_close_time') ? 'border-red-500' : 'border-gray-200' }} text-gray-900 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                            @error('attendance_close_time') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- ── Pengaturan Pelanggaran & Peringatan ── --}}
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-1 flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">
                                Batas Peringatan 1 <span class="text-red-600">*</span>
                            </label>
                            <input type="number" name="violation_warning1" min="1" max="999"
                                   value="{{ old('violation_warning1', $school->violation_warning1 ?? 10) }}"
                                   class="w-full bg-white border {{ $errors->has('violation_warning1') ? 'border-red-500' : 'border-amber-200' }} text-gray-900 rounded-xl px-4 py-2.5 text-sm font-semibold focus:outline-none focus:border-amber-500 transition-colors">
                            <p class="text-xs text-amber-600 mt-1">poin · Peringatan 1</p>
                            @error('violation_warning1') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">
                                Batas Peringatan 2 <span class="text-red-600">*</span>
                            </label>
                            <input type="number" name="violation_warning2" min="1" max="999"
                                   value="{{ old('violation_warning2', $school->violation_warning2 ?? 20) }}"
                                   class="w-full bg-white border {{ $errors->has('violation_warning2') ? 'border-red-500' : 'border-orange-200' }} text-gray-900 rounded-xl px-4 py-2.5 text-sm font-semibold focus:outline-none focus:border-orange-500 transition-colors">
                            <p class="text-xs text-orange-600 mt-1">poin · Peringatan 2</p>
                            @error('violation_warning2') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">
                                Batas Peringatan 3 <span class="text-red-600">*</span>
                            </label>
                            <input type="number" name="violation_warning3" min="1" max="999"
                                   value="{{ old('violation_warning3', $school->violation_warning3 ?? 30) }}"
                                   class="w-full bg-white border {{ $errors->has('violation_warning3') ? 'border-red-500' : 'border-red-200' }} text-gray-900 rounded-xl px-4 py-2.5 text-sm font-semibold focus:outline-none focus:border-red-500 transition-colors">
                            <p class="text-xs text-red-600 mt-1">poin · Peringatan 3</p>
                            @error('violation_warning3') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Batas alfa per semester --}}
                    <div class="border-t border-gray-200 pt-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">
                                    Batas Alfa per Semester
                                </label>
                                <input type="number" name="alfa_limit_per_semester" min="0" max="999"
                                       value="{{ old('alfa_limit_per_semester', $school->alfa_limit_per_semester ?? 0) }}"
                                       class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                                <p class="text-gray-500 text-xs mt-1">
                                    hari · Isi 0 untuk menonaktifkan batas
                                </p>
                                @error('alfa_limit_per_semester') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="bg-white border border-gray-200 rounded-xl p-3 text-xs text-gray-500">
                                <p class="font-semibold text-gray-900 mb-1">Cara kerja alfa:</p>
                                <p>Poin dari alfa berlaku selama siswa sekolah ({{ $school->school_program_years ?? 3 }} tahun), tidak reset per semester.</p>
                                <p class="mt-1">Batas alfa per semester hanya untuk monitoring kesiswaan, tidak otomatis memblokir siswa.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Poin Pelanggaran Prakerin --}}
                @if($school->feature_prakerin)
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-1">Poin Pelanggaran Prakerin</h2>
                    <p class="text-gray-500 text-xs mb-4">Poin otomatis diberikan tiap hari jika siswa tidak absen/tidak isi jurnal</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Tidak Absen Masuk</label>
                            <div class="flex items-center gap-2">
                                <input type="number" name="prakerin_points_no_checkin" min="0" max="99"
                                       value="{{ old('prakerin_points_no_checkin', $school->prakerin_points_no_checkin ?? 2) }}"
                                       class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                                <span class="text-gray-500 text-sm whitespace-nowrap">poin</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Tidak Absen Pulang</label>
                            <div class="flex items-center gap-2">
                                <input type="number" name="prakerin_points_no_checkout" min="0" max="99"
                                       value="{{ old('prakerin_points_no_checkout', $school->prakerin_points_no_checkout ?? 1) }}"
                                       class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                                <span class="text-gray-500 text-sm whitespace-nowrap">poin</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Tidak Isi Jurnal</label>
                            <div class="flex items-center gap-2">
                                <input type="number" name="prakerin_points_no_journal" min="0" max="99"
                                       value="{{ old('prakerin_points_no_journal', $school->prakerin_points_no_journal ?? 1) }}"
                                       class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                                <span class="text-gray-500 text-sm whitespace-nowrap">poin</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Paket Aktif</h2>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-bold
                            {{ $school->package === 'enterprise' ? 'bg-blue-50 text-blue-600 border border-blue-200' :
                               ($school->package === 'pro' ? 'bg-blue-50 text-blue-400 border border-blue-200' :
                               'bg-gray-50 text-gray-500 border border-gray-200') }}">
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
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                        </svg>
                        Lokasi GPS Sekolah
                    </h2>

                    @if($school->latitude && $school->longitude)
                        <div class="flex items-center gap-2 mb-3 text-xs text-blue-600">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            GPS sudah diatur
                        </div>
                    @else
                        <div class="flex items-center gap-2 mb-3 text-xs text-red-600">
                            <span class="w-2 h-2 rounded-full bg-red-400"></span>
                            GPS belum diatur
                        </div>
                    @endif

                    <div class="space-y-3 mb-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Latitude</label>
                            <input type="number" name="latitude" id="input-latitude" step="0.00000001"
                                   value="{{ old('latitude', $school->latitude) }}"
                                   class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors"
                                   placeholder="-7.324406">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Longitude</label>
                            <input type="number" name="longitude" id="input-longitude" step="0.00000001"
                                   value="{{ old('longitude', $school->longitude) }}"
                                   class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors"
                                   placeholder="110.969947">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">
                                Radius Absensi (meter) <span class="text-red-600">*</span>
                            </label>
                            <input type="number" name="attendance_radius_meters"
                                   value="{{ old('attendance_radius_meters', $school->attendance_radius_meters) }}"
                                   min="50" max="1000"
                                   class="w-full bg-white border {{ $errors->has('attendance_radius_meters') ? 'border-red-500' : 'border-gray-200' }} text-gray-900 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                            <p class="text-gray-500 text-xs mt-1">Minimal 50m, maksimal 1000m. Rekomendasi: 200m</p>
                            @error('attendance_radius_meters') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <button type="button" id="btn-detect-location"
                            class="w-full flex items-center justify-center gap-2 bg-blue-50 hover:bg-blue-50 border border-blue-200 text-blue-400 text-sm font-medium py-2.5 rounded-xl transition-colors mb-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                        </svg>
                        Gunakan Lokasi Saat Ini
                    </button>

                    <p id="detect-status" class="text-xs text-center text-gray-500"></p>

                    <p class="text-gray-500 text-xs mt-3 leading-relaxed">
                        Klik tombol di atas saat berada di area sekolah untuk mengisi koordinat otomatis.
                        Atau isi manual dari Google Maps.
                    </p>
                </div>
            </div>
        </div>

        {{-- Jam Absensi Guru --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-1">Jam Absensi Guru</h2>
            <p class="text-xs text-gray-500 mb-4">Sesi masuk dan pulang dibuat otomatis sesuai jam di bawah.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Buka Absen Masuk</label>
                    <input type="time" name="teacher_checkin_open"
                           value="{{ substr($school->teacher_checkin_open ?? '06:30', 0, 5) }}"
                           class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Batas Terlambat Masuk</label>
                    <input type="time" name="teacher_checkin_late"
                           value="{{ substr($school->teacher_checkin_late ?? '07:15', 0, 5) }}"
                           class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Tutup Absen Masuk</label>
                    <input type="time" name="teacher_checkin_close"
                           value="{{ substr($school->teacher_checkin_close ?? '08:00', 0, 5) }}"
                           class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Buka Absen Pulang</label>
                    <input type="time" name="teacher_checkout_open"
                           value="{{ substr($school->teacher_checkout_open ?? '14:00', 0, 5) }}"
                           class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Tutup Absen Pulang</label>
                    <input type="time" name="teacher_checkout_close"
                           value="{{ substr($school->teacher_checkout_close ?? '16:00', 0, 5) }}"
                           class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                </div>
                <div class="flex items-end">
                    <a href="{{ route('admin.teacher-attendance.qr') }}"
                       target="_blank"
                       class="w-full flex items-center justify-center gap-2 bg-white hover:bg-gray-50 border border-gray-200 text-gray-600 text-sm px-4 py-2.5 rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
                        </svg>
                        Cetak QR Guru
                    </a>
                </div>

                {{-- Refresh QR — pakai JS fetch agar tidak nested form --}}
                <div class="sm:col-span-3 mt-2">
                    <button type="button" id="btn-refresh-qr"
                            class="flex items-center gap-2 text-sm text-amber-600 hover:text-amber-700 bg-amber-50 border border-amber-200 px-4 py-2 rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                        </svg>
                        Perbarui QR Guru
                    </button>
                    <p class="text-xs text-gray-500 mt-1">Perbarui QR jika QR lama hilang atau rusak. Cetak ulang QR setelah diperbarui.</p>
                </div>
            </div>
        </div>

        {{-- Tombol simpan --}}
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('admin.dashboard') }}"
               class="px-6 py-2.5 text-sm font-medium rounded-xl bg-white hover:bg-gray-50 text-gray-600 border border-gray-200 transition-colors">
                Batal
            </a>
            <button type="submit"
                    class="px-6 py-2.5 text-sm font-semibold rounded-xl bg-blue-600 hover:bg-blue-700 text-white transition-colors">
                Simpan Pengaturan
            </button>
        </div>
    </form>

    <script>
    document.getElementById('btn-refresh-qr')?.addEventListener('click', function() {
        if (!confirm('QR lama tidak berlaku setelah diperbarui. Pastikan cetak QR baru setelah ini. Lanjutkan?')) return;
        var btn = this;
        btn.disabled = true;
        btn.textContent = 'Memperbarui...';
        fetch('{{ route("admin.teacher-attendance.refresh-qr") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        })
        .then(r => r.ok ? (btn.textContent = 'QR Diperbarui ✓', setTimeout(() => location.reload(), 1000)) : Promise.reject())
        .catch(() => { btn.disabled = false; btn.textContent = 'Perbarui QR Guru'; alert('Gagal memperbarui QR.'); });
    });
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
                statusEl.className   = 'text-xs text-center text-blue-600';
                document.getElementById('btn-detect-location').disabled = false;
            },
            function(err) {
                var msgs = { 1: 'Izinkan akses lokasi di browser.', 2: 'GPS tidak tersedia.', 3: 'GPS timeout. Coba lagi.' };
                statusEl.textContent = msgs[err.code] || 'Gagal deteksi lokasi.';
                statusEl.className   = 'text-xs text-center text-red-600';
                document.getElementById('btn-detect-location').disabled = false;
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    });
    </script>

</x-simans-layout>