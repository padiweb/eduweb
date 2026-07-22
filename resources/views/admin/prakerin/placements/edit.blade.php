<x-simans-layout title="Edit Penempatan Prakerin">

    <div class="mb-6">
        <a href="{{ route('admin.prakerin.placements.index') }}"
           class="text-gray-500 text-sm hover:text-gray-900 flex items-center gap-1 mb-3 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Edit Penempatan Prakerin</h1>
        <p class="text-gray-500 text-sm mt-1">{{ $placement->student->name }}</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
            <ul class="space-y-0.5">
                @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.prakerin.placements.update', $placement) }}" method="POST">
        @csrf @method('PUT')

        <div class="space-y-4">

            {{-- Info siswa (readonly) --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Data Siswa</h2>
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                    <div class="w-8 h-8 rounded-full bg-emerald-900 border border-emerald-700/50 flex items-center justify-center text-xs font-bold text-blue-600">
                        {{ substr($placement->student->name, 0, 1) }}
                    </div>
                    <div>
                        <p class="text-gray-900 text-sm font-medium">{{ $placement->student->name }}</p>
                        <p class="text-gray-500 text-xs">{{ $placement->academicYear?->label }}</p>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Guru Pembimbing</label>
                    <select name="supervisor_teacher_id"
                            class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                        <option value="">— Tidak ada —</option>
                        @foreach ($teachers as $t)
                            <option value="{{ $t->id }}"
                                    {{ old('supervisor_teacher_id', $placement->supervisor_teacher_id) == $t->id ? 'selected' : '' }}>
                                {{ $t->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Data DU/DI --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Data DU/DI</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Nama DU/DI <span class="text-red-400">*</span></label>
                        <input type="text" name="company_name"
                               value="{{ old('company_name', $placement->company_name) }}"
                               required maxlength="150"
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Alamat DU/DI</label>
                        <textarea name="company_address" rows="2"
                                  class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors resize-none">{{ old('company_address', $placement->company_address) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Latitude</label>
                        <input type="number" name="latitude" id="input-lat"
                               value="{{ old('latitude', $placement->latitude) }}"
                               step="0.00000001"
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Longitude</label>
                        <input type="number" name="longitude" id="input-lng"
                               value="{{ old('longitude', $placement->longitude) }}"
                               step="0.00000001"
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Radius Check-in (meter) <span class="text-red-400">*</span></label>
                        <input type="number" name="radius_meters"
                               value="{{ old('radius_meters', $placement->radius_meters) }}"
                               min="50" max="2000" required
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                    <div class="flex items-end">
                        <button type="button" onclick="detectLocation()"
                                class="w-full py-2.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-400 text-sm rounded-xl transition-colors">
                            Deteksi Lokasi
                        </button>
                    </div>
                    <p id="gps-status" class="text-gray-500 text-xs md:col-span-2"></p>
                </div>
            </div>

            {{-- Pembimbing Lapangan --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Pembimbing Lapangan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Nama</label>
                        <input type="text" name="field_supervisor_name"
                               value="{{ old('field_supervisor_name', $placement->field_supervisor_name) }}"
                               maxlength="100"
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">No. HP</label>
                        <input type="text" name="field_supervisor_phone"
                               value="{{ old('field_supervisor_phone', $placement->field_supervisor_phone) }}"
                               maxlength="20"
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                </div>
            </div>

            {{-- Periode & Status --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Periode & Status</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Tanggal Mulai <span class="text-red-400">*</span></label>
                        <input type="date" name="start_date"
                               value="{{ old('start_date', $placement->start_date->format('Y-m-d')) }}" required
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Tanggal Selesai <span class="text-red-400">*</span></label>
                        <input type="date" name="end_date"
                               value="{{ old('end_date', $placement->end_date->format('Y-m-d')) }}" required
                               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                    <div class="flex items-center gap-3 pt-5">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1"
                                   {{ old('is_active', $placement->is_active) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded accent-emerald-500">
                            <span class="text-sm text-gray-400">Aktif</span>
                        </label>
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
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>

    <script>
        function detectLocation() {
            const status = document.getElementById('gps-status');
            status.textContent = 'Mendeteksi...';
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    document.getElementById('input-lat').value = pos.coords.latitude.toFixed(8);
                    document.getElementById('input-lng').value = pos.coords.longitude.toFixed(8);
                    status.textContent = 'Lokasi terdeteksi! ±' + Math.round(pos.coords.accuracy) + 'm';
                    status.className = 'text-blue-600 text-xs md:col-span-2';
                },
                () => {
                    status.textContent = 'Gagal deteksi. Isi manual.';
                    status.className = 'text-red-400 text-xs md:col-span-2';
                },
                { enableHighAccuracy: true, timeout: 15000 }
            );
        }
    </script>

</x-simans-layout>
