{{-- Partial: form fields DU/DI, dipakai di modal tambah dan edit --}}
<div>
    <label class="block text-xs text-gray-500 mb-1">Nama DU/DI <span class="text-red-400">*</span></label>
    <input type="text" name="name" required maxlength="150" placeholder="PT. Contoh Maju Jaya"
           class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 placeholder-gray-400">
</div>
<div>
    <label class="block text-xs text-gray-500 mb-1">Alamat</label>
    <textarea name="address" rows="2" placeholder="Alamat lengkap DU/DI"
              class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 resize-none placeholder-gray-400"></textarea>
</div>

{{-- Koordinat GPS --}}
<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="block text-xs text-gray-500 mb-1">Latitude</label>
        <input type="number" name="latitude" id="{{ ($isEdit ?? false) ? 'edit-lat' : 'add-lat' }}"
               step="0.00000001" placeholder="-7.32440"
               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 placeholder-gray-400">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Longitude</label>
        <input type="number" name="longitude" id="{{ ($isEdit ?? false) ? 'edit-lng' : 'add-lng' }}"
               step="0.00000001" placeholder="110.96994"
               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 placeholder-gray-400">
    </div>
</div>
<div class="flex items-center gap-3">
    <div class="flex-1">
        <label class="block text-xs text-gray-500 mb-1">Radius Check-in (meter) <span class="text-red-400">*</span></label>
        <input type="number" name="radius_meters" value="300" min="50" max="2000" required
               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
    </div>
    <div class="flex-1 pt-5">
        <button type="button"
                onclick="detectGps('{{ ($isEdit ?? false) ? 'edit-lat' : 'add-lat' }}', '{{ ($isEdit ?? false) ? 'edit-lng' : 'add-lng' }}', '{{ ($isEdit ?? false) ? 'edit-gps-status' : 'add-gps-status' }}')"
                class="w-full py-2.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-400 text-sm rounded-xl transition-colors">
            📍 Deteksi Lokasi
        </button>
    </div>
</div>
<p id="{{ ($isEdit ?? false) ? 'edit-gps-status' : 'add-gps-status' }}" class="text-gray-500 text-xs -mt-1"></p>

{{-- Jam --}}
<div class="grid grid-cols-3 gap-3">
    <div>
        <label class="block text-xs text-gray-500 mb-1">Jam Masuk</label>
        <input type="time" name="checkin_time"
               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
        <p class="text-gray-500 text-xs mt-0.5">Jam masuk DU/DI ini</p>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Batas Toleransi</label>
        <input type="time" name="checkin_late_after"
               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
        <p class="text-gray-500 text-xs mt-0.5">Setelah ini = terlambat</p>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Jam Pulang</label>
        <input type="time" name="checkout_time"
               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500">
    </div>
</div>

{{-- Pembimbing Lapangan dari DU/DI --}}
<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="block text-xs text-gray-500 mb-1">Nama Pembimbing Lapangan</label>
        <input type="text" name="field_supervisor_name" maxlength="100" placeholder="Dari perusahaan"
               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 placeholder-gray-400">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">No. HP</label>
        <input type="text" name="field_supervisor_phone" maxlength="20" placeholder="08xxxxxxxxxx"
               class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 placeholder-gray-400">
    </div>
</div>

{{-- Guru Pembimbing (dari sekolah) --}}
<div>
    <label class="block text-xs text-gray-500 mb-2">Guru Pembimbing (dari sekolah, bisa lebih dari 1)</label>
    <div class="space-y-1 max-h-40 overflow-y-auto pr-1">
        @foreach ($teachers as $t)
            <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-gray-50">
                <input type="checkbox" name="teacher_ids[]" value="{{ $t->id }}"
                       class="w-4 h-4 rounded accent-emerald-500">
                <span class="text-sm text-gray-400">{{ $t->name }}</span>
                <span class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $t->role)) }}</span>
            </label>
        @endforeach
    </div>
</div>
