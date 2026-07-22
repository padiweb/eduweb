<x-simans-layout title="Tambah Penempatan Prakerin">

    <div class="mb-6">
        <a href="{{ route('admin.prakerin.placements.index') }}"
           class="text-gray-500 text-sm hover:text-gray-900 flex items-center gap-1 mb-3 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Tambah Penempatan Prakerin</h1>
        <p class="text-gray-500 text-sm mt-1">Assign siswa ke tempat praktik industri</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
            <ul class="space-y-0.5">
                @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.prakerin.placements.store') }}" method="POST">
        @csrf

        <div class="space-y-4">

            {{-- Siswa & Tahun Ajaran --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Data Siswa</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Siswa <span class="text-red-400">*</span></label>
                        <select name="student_id" required
                                class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                            <option value="">— Pilih Siswa —</option>
                            @foreach ($students as $s)
                                <option value="{{ $s->id }}" {{ old('student_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-gray-500 text-xs mt-1">Hanya siswa tanpa penempatan aktif yang tampil</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Tahun Ajaran <span class="text-red-400">*</span></label>
                        <select name="academic_year_id" required
                                class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                            <option value="">— Pilih Tahun Ajaran —</option>
                            @foreach ($years as $y)
                                <option value="{{ $y->id }}" {{ old('academic_year_id') == $y->id || $y->is_active ? 'selected' : '' }}>
                                    {{ $y->label }} {{ $y->is_active ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Guru Pembimbing</label>
                        <select name="supervisor_teacher_id"
                                class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                            <option value="">— Pilih Guru (opsional) —</option>
                            @foreach ($teachers as $t)
                                <option value="{{ $t->id }}" {{ old('supervisor_teacher_id') == $t->id ? 'selected' : '' }}>
                                    {{ $t->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Data DU/DI --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Data DU/DI (Tempat Praktik)</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Nama DU/DI <span class="text-red-400">*</span></label>
                        <input type="text" name="company_name" value="{{ old('company_name') }}"
                               placeholder="Contoh: PT. Sumber Makmur Abadi"
                               required maxlength="150"
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors placeholder-gray-400">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Alamat DU/DI</label>
                        <textarea name="company_address" rows="2"
                                  placeholder="Alamat lengkap tempat praktik"
                                  class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors placeholder-gray-400 resize-none">{{ old('company_address') }}</textarea>
                    </div>

                    {{-- Koordinat GPS --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Latitude</label>
                        <input type="number" name="latitude" id="input-lat" value="{{ old('latitude') }}"
                               step="0.00000001" placeholder="-7.32440"
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors placeholder-gray-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Longitude</label>
                        <input type="number" name="longitude" id="input-lng" value="{{ old('longitude') }}"
                               step="0.00000001" placeholder="110.96994"
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors placeholder-gray-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Radius Check-in (meter) <span class="text-red-400">*</span></label>
                        <input type="number" name="radius_meters" value="{{ old('radius_meters', 300) }}"
                               min="50" max="2000" required
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                        <p class="text-gray-500 text-xs mt-1">Siswa harus berada dalam radius ini untuk absen</p>
                    </div>
                    <div class="flex items-end">
                        <button type="button" onclick="detectLocation()"
                                id="btn-detect"
                                class="w-full py-2.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-400 text-sm rounded-xl transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                            Deteksi Lokasi Sekarang
                        </button>
                    </div>
                    <div class="md:col-span-2">
                        <p id="gps-status" class="text-gray-500 text-xs"></p>
                    </div>
                </div>
            </div>

            {{-- Pembimbing Lapangan --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Pembimbing Lapangan (dari DU/DI)</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Nama Pembimbing Lapangan</label>
                        <input type="text" name="field_supervisor_name" value="{{ old('field_supervisor_name') }}"
                               maxlength="100" placeholder="Nama pembimbing dari perusahaan"
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors placeholder-gray-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">No. HP Pembimbing Lapangan</label>
                        <input type="text" name="field_supervisor_phone" value="{{ old('field_supervisor_phone') }}"
                               maxlength="20" placeholder="08xxxxxxxxxx"
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors placeholder-gray-400">
                    </div>
                </div>
            </div>

            {{-- Periode --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Periode Prakerin</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Tanggal Mulai <span class="text-red-400">*</span></label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" required
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Tanggal Selesai <span class="text-red-400">*</span></label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" required
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                </div>
            </div>

            <div class="flex gap-3 justify-end">
                <a href="{{ route('admin.prakerin.placements.index') }}"
                   class="px-5 py-2.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-400 text-sm rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors">
                    Simpan Penempatan
                </button>
            </div>
        </div>
    </form>

    <script>
        function detectLocation() {
            const btn = document.getElementById('btn-detect');
            const status = document.getElementById('gps-status');
            btn.disabled = true;
            status.textContent = 'Mendeteksi lokasi...';
            status.className = 'text-gray-500 text-xs';

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    document.getElementById('input-lat').value = pos.coords.latitude.toFixed(8);
                    document.getElementById('input-lng').value = pos.coords.longitude.toFixed(8);
                    status.textContent = 'Lokasi terdeteksi! Akurasi: ±' + Math.round(pos.coords.accuracy) + 'm';
                    status.className = 'text-blue-600 text-xs';
                    btn.disabled = false;
                },
                (err) => {
                    status.textContent = 'Gagal deteksi lokasi. Isi koordinat manual.';
                    status.className = 'text-red-400 text-xs';
                    btn.disabled = false;
                },
                { enableHighAccuracy: true, timeout: 15000 }
            );
        }
    </script>

</x-simans-layout>
