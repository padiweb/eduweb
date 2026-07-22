<x-simans-layout title="DU/DI Saya - Prakerin">

    <div class="mb-5">
        <h1 class="text-xl font-bold text-gray-900">DU/DI yang Saya Bimbing</h1>
        <p class="text-gray-500 text-sm mt-0.5">{{ $period?->name ?? 'Tidak ada periode aktif' }}</p>
    </div>

    {{-- Sub-nav --}}
    <div class="flex gap-2 mb-5 overflow-x-auto pb-1 -mx-4 px-4 scrollbar-none">
        <a href="{{ route('guru.prakerin.index') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-blue-600 transition-colors">Dashboard</a>
        <a href="{{ route('guru.prakerin.locations') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-blue-600 text-white">DU/DI Saya</a>
        <a href="{{ route('guru.prakerin.placements') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-blue-600 transition-colors">Penempatan Siswa</a>
        <a href="{{ route('guru.prakerin.recap.absensi') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-blue-600 transition-colors">Rekap Absensi</a>
        <a href="{{ route('guru.prakerin.recap.jurnal') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-blue-600 transition-colors">Rekap Jurnal</a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-600 text-sm">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-sm space-y-0.5">
            @foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
    @endif

    @if (! $period)
        <div class="bg-white border border-gray-200 rounded-xl p-10 text-center">
            <p class="text-gray-500 text-sm">Anda belum menjadi pembimbing di DU/DI manapun.</p>
            <p class="text-gray-500 text-xs mt-1">Hubungi admin atau koordinator untuk ditambahkan.</p>
        </div>
    @elseif ($locations->isEmpty())
        <div class="bg-white border border-gray-200 rounded-xl p-10 text-center">
            <p class="text-gray-500 text-sm">Belum ada DU/DI yang Anda bimbing di periode ini.</p>
        </div>
    @else
        {{-- Daftar DU/DI --}}
        <div class="space-y-3">
            @foreach ($locations as $loc)
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-gray-900 font-semibold">{{ $loc->name }}</p>
                            @if ($loc->address)
                                <p class="text-gray-500 text-sm mt-0.5">{{ $loc->address }}</p>
                            @endif
                            @if ($loc->field_supervisor_name)
                                <p class="text-gray-500 text-xs mt-1">
                                    Pembimbing lapangan: {{ $loc->field_supervisor_name }}
                                    @if ($loc->field_supervisor_phone) &middot; {{ $loc->field_supervisor_phone }} @endif
                                </p>
                            @endif

                            {{-- Info jam & GPS --}}
                            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-xs">
                                @if ($loc->checkin_time)
                                    <span class="text-gray-500">
                                        Masuk: <span class="text-gray-600">{{ $loc->checkin_time }}</span>
                                        @if ($loc->checkin_late_after)
                                            &middot; Toleransi: <span class="text-gray-600">{{ $loc->checkin_late_after }}</span>
                                        @endif
                                    </span>
                                @endif
                                @if ($loc->checkout_time)
                                    <span class="text-gray-500">Pulang: <span class="text-gray-600">{{ $loc->checkout_time }}</span></span>
                                @endif
                                @if ($loc->latitude)
                                    <span class="text-blue-600">GPS aktif</span>
                                @else
                                    <span class="text-amber-600">GPS belum diset</span>
                                @endif
                                <span class="text-gray-500">Siswa: <span class="text-gray-600">{{ $loc->placements->count() }}</span></span>
                            </div>

                            {{-- Guru pembimbing --}}
                            @if ($loc->supervisors->count() > 0)
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    @foreach ($loc->supervisors as $s)
                                        <span class="px-2.5 py-0.5 text-xs rounded-lg border
                                            {{ $s->id === auth()->id()
                                                ? 'bg-blue-50 border-emerald-200 text-blue-600'
                                                : 'bg-white border-gray-200 text-gray-500' }}">
                                            {{ $s->name }}{{ $s->id === auth()->id() ? ' (Anda)' : '' }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <button onclick="openEdit({{ $loc->id }})"
                                class="flex-shrink-0 px-3 py-1.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-600 text-xs rounded-lg transition-colors">
                            Edit
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Modal Edit DU/DI --}}
    <div id="modal-edit" class="hidden fixed inset-0 z-50 bg-black/60 overflow-y-auto">
        <div class="min-h-full flex items-start justify-center p-4 py-8">
            <div class="bg-white border border-gray-200 rounded-xl w-full max-w-lg">

                {{-- Modal header --}}
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
                    <h2 class="text-gray-900 font-semibold" id="modal-title">Edit DU/DI</h2>
                    <button onclick="closeModal()" class="text-gray-500 hover:text-blue-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Form --}}
                <form id="form-edit" method="POST" class="divide-y divide-gray-100">
                    @csrf @method('PUT')

                    {{-- Identitas --}}
                    <div class="px-5 py-5 space-y-3">
                        <p class="text-xs font-bold text-gray-600 uppercase tracking-wider">Identitas DU/DI</p>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1.5">Nama DU/DI <span class="text-red-600">*</span></label>
                            <input type="text" name="name" id="e-name" required maxlength="150"
                                   class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-200">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1.5">Alamat</label>
                            <textarea name="address" id="e-address" rows="2"
                                      class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-200 resize-none"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1.5">Nama Pembimbing Lapangan</label>
                                <input type="text" name="field_supervisor_name" id="e-sv-name" maxlength="100"
                                       placeholder="Dari perusahaan"
                                       class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-200 placeholder-gray-700">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1.5">No. HP</label>
                                <input type="text" name="field_supervisor_phone" id="e-sv-phone" maxlength="20"
                                       placeholder="08xxxxxxxxxx"
                                       class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-200 placeholder-gray-700">
                            </div>
                        </div>
                    </div>

                    {{-- GPS --}}
                    <div class="px-5 py-5 space-y-3">
                        <p class="text-xs font-bold text-gray-600 uppercase tracking-wider">Koordinat GPS</p>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1.5">Latitude</label>
                                <input type="number" name="latitude" id="e-lat" step="0.00000001" placeholder="-7.32440"
                                       class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-200 placeholder-gray-700">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1.5">Longitude</label>
                                <input type="number" name="longitude" id="e-lng" step="0.00000001" placeholder="110.96994"
                                       class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-200 placeholder-gray-700">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1.5">Radius Check-in (meter)</label>
                                <input type="number" name="radius_meters" id="e-radius" min="50" max="2000"
                                       class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-200">
                            </div>
                            <div class="flex flex-col justify-end">
                                <button type="button" onclick="detectGps()"
                                        class="w-full py-2.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-600 text-sm rounded-xl transition-colors">
                                    Deteksi Lokasi
                                </button>
                            </div>
                        </div>
                        <p id="gps-status" class="text-xs text-gray-500"></p>
                    </div>

                    {{-- Jam --}}
                    <div class="px-5 py-5 space-y-3">
                        <p class="text-xs font-bold text-gray-600 uppercase tracking-wider">Jam Kehadiran</p>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1.5">Jam Masuk</label>
                                <input type="time" name="checkin_time" id="e-checkin"
                                       class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-200">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1.5">Toleransi</label>
                                <input type="time" name="checkin_late_after" id="e-late"
                                       class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-200">
                                <p class="text-gray-500 text-xs mt-1">Setelah ini terlambat</p>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1.5">Jam Pulang</label>
                                <input type="time" name="checkout_time" id="e-checkout"
                                       class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-200">
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="px-5 py-4 flex gap-3">
                        <button type="button" onclick="closeModal()"
                                class="flex-1 py-2.5 bg-white border border-gray-200 text-gray-600 text-sm rounded-xl hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 py-2.5 bg-blue-700 hover:bg-blue-600 text-white text-sm font-semibold rounded-xl transition-colors">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Data lokasi untuk JS --}}
    <script>
        const LOCATIONS = @json($locationsJs);

        function openEdit(locId) {
            const loc = LOCATIONS.find(l => l.id === locId);
            if (!loc) return;

            document.getElementById('modal-title').textContent  = 'Edit: ' + loc.name;
            document.getElementById('form-edit').action         = '/guru/prakerin/locations/' + loc.id;
            document.getElementById('e-name').value             = loc.name || '';
            document.getElementById('e-address').value          = loc.address || '';
            document.getElementById('e-sv-name').value          = loc.field_supervisor_name || '';
            document.getElementById('e-sv-phone').value         = loc.field_supervisor_phone || '';
            document.getElementById('e-lat').value              = loc.latitude || '';
            document.getElementById('e-lng').value              = loc.longitude || '';
            document.getElementById('e-radius').value           = loc.radius_meters || 300;
            document.getElementById('e-checkin').value          = loc.checkin_time || '';
            document.getElementById('e-late').value             = loc.checkin_late_after || '';
            document.getElementById('e-checkout').value         = loc.checkout_time || '';

            const gpsEl = document.getElementById('gps-status');
            gpsEl.textContent  = loc.latitude ? 'GPS sudah diset' : 'Belum ada koordinat GPS';
            gpsEl.className    = 'text-xs ' + (loc.latitude ? 'text-blue-600' : 'text-gray-500');

            document.getElementById('modal-edit').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('modal-edit').classList.add('hidden');
        }

        document.getElementById('modal-edit').addEventListener('click', function(e) {
            if (e.target === this || e.target.classList.contains('min-h-full')) closeModal();
        });

        function detectGps() {
            const el = document.getElementById('gps-status');
            el.textContent = 'Mendeteksi lokasi...';
            el.className = 'text-xs text-amber-600';
            navigator.geolocation.getCurrentPosition(
                pos => {
                    document.getElementById('e-lat').value = pos.coords.latitude.toFixed(8);
                    document.getElementById('e-lng').value = pos.coords.longitude.toFixed(8);
                    el.textContent = 'Lokasi terdeteksi (\u00b1' + Math.round(pos.coords.accuracy) + 'm)';
                    el.className = 'text-xs text-blue-600';
                },
                () => {
                    el.textContent = 'Gagal mendeteksi. Isi koordinat manual.';
                    el.className = 'text-xs text-red-600';
                },
                { enableHighAccuracy: true, timeout: 15000 }
            );
        }
    </script>

</x-simans-layout>
