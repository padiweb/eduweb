<x-simans-layout title="DU/DI Prakerin">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tempat Prakerin (DU/DI)</h1>
            <p class="text-gray-500 text-sm mt-1">Kelola tempat praktik industri per periode</p>
        </div>
        @if ($activePeriod)
        <button onclick="document.getElementById('modal-tambah').classList.remove('hidden')"
                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah DU/DI
        </button>
        @endif
    </div>

    {{-- Sub-nav --}}
    <div class="tab-nav-scroll">
        <a href="{{ route('admin.prakerin.periods.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-blue-600 transition-colors">Periode</a>
        <a href="{{ route('admin.prakerin.locations.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-blue-600 text-white">DU/DI</a>
        <a href="{{ route('admin.prakerin.placements.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-blue-600 transition-colors">Penempatan Siswa</a>
        <a href="{{ route('admin.prakerin.recap.absensi') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-blue-600 transition-colors">Rekap Absensi</a>
        <a href="{{ route('admin.prakerin.recap.jurnal') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-500 hover:text-blue-600 transition-colors">Rekap Jurnal</a>
    </div>

    {{-- Filter periode --}}
    <div class="tab-nav-scroll">
        @foreach ($periods as $p)
            <a href="{{ route('admin.prakerin.locations.index', ['period_id' => $p->id]) }}"
               class="px-4 py-1.5 rounded-xl text-sm font-medium transition-colors flex items-center gap-2
               {{ $periodId == $p->id ? 'bg-blue-600 text-white' : 'bg-white border border-gray-200 text-gray-500 hover:text-gray-700' }}">
                {{ $p->name }}
                @if ($p->isOngoing()) <span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span> @endif
            </a>
        @endforeach
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-600 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-sm">{{ session('error') }}</div>
    @endif

    @if (! $activePeriod)
        <div class="bg-white border border-gray-200 rounded-xl p-8 text-center">
            <p class="text-gray-500">Pilih periode di atas atau <a href="{{ route('admin.prakerin.periods.index') }}" class="text-blue-600 hover:underline">buat periode baru</a>.</p>
        </div>
    @elseif ($locations->isEmpty())
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
            <p class="text-gray-500">Belum ada DU/DI untuk periode ini.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($locations as $loc)
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-1">
                                <h3 class="text-gray-900 font-semibold">{{ $loc->name }}</h3>
                                @if (! $loc->is_active)
                                    <span class="px-2 py-0.5 rounded-lg text-xs bg-gray-50 text-gray-500">Nonaktif</span>
                                @endif
                            </div>
                            @if ($loc->address)
                                <p class="text-gray-500 text-sm">{{ $loc->address }}</p>
                            @endif
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-xs text-gray-500">
                                @if ($loc->checkin_time)
                                    <span>Masuk: <span class="text-gray-600">{{ $loc->checkin_time }}</span>
                                    @if ($loc->checkin_late_after) · Toleransi: <span class="text-gray-600">{{ $loc->checkin_late_after }}</span> @endif
                                    </span>
                                @endif
                                @if ($loc->checkout_time)
                                    <span>Pulang: <span class="text-gray-600">{{ $loc->checkout_time }}</span></span>
                                @endif
                                <span>Radius: <span class="text-gray-600">{{ $loc->radius_meters }}m</span></span>
                                <span>Siswa: <span class="text-gray-600">{{ $loc->placements->count() }}</span></span>
                            </div>
                            @if ($loc->supervisors->count() > 0)
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @foreach ($loc->supervisors as $s)
                                        <span class="px-2 py-0.5 bg-blue-50 border border-blue-200 text-blue-400 text-xs rounded-lg">
                                            {{ $s->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            <button onclick="openEditLoc({{ $loc->id }}, {{ json_encode($loc->toArray()) }}, {{ json_encode($loc->supervisors->pluck('id')) }})"
                                    class="px-3 py-1.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-600 text-xs rounded-lg transition-colors">
                                Edit
                            </button>
                            @if ($loc->placements->count() === 0)
                            <form action="{{ route('admin.prakerin.locations.destroy', $loc) }}" method="POST"
                                  onsubmit="return confirm('Hapus DU/DI ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 bg-red-50 hover:bg-red-50 border border-red-200 text-red-600 text-xs rounded-lg transition-colors">
                                    Hapus
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Modal Tambah --}}
    <div id="modal-tambah" class="hidden fixed inset-0 z-50 bg-black/60 overflow-y-auto">
        <div class="min-h-full flex items-start justify-center p-4 py-8">
        <div class="bg-white border border-gray-200 rounded-xl p-6 w-full max-w-lg">
            <h2 class="text-gray-900 font-semibold mb-4">Tambah DU/DI</h2>
            <form action="{{ route('admin.prakerin.locations.store') }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="period_id" value="{{ $periodId }}">
                @include('admin.prakerin.locations._form')
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-tambah').classList.add('hidden')"
                            class="flex-1 py-2.5 bg-white border border-gray-200 text-gray-600 text-sm rounded-xl">Batal</button>
                    <button type="submit" class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl">Simpan</button>
                </div>
            </form>
        </div>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div id="modal-edit" class="hidden fixed inset-0 z-50 bg-black/60 overflow-y-auto">
        <div class="min-h-full flex items-start justify-center p-4 py-8">
        <div class="bg-white border border-gray-200 rounded-xl p-6 w-full max-w-lg">
            <h2 class="text-gray-900 font-semibold mb-4">Edit DU/DI</h2>
            <form id="form-edit-loc" method="POST" class="space-y-3">
                @csrf @method('PUT')
                @include('admin.prakerin.locations._form', ['isEdit' => true])
                <label class="flex items-center gap-2 cursor-pointer pt-1">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" id="edit-is-active" name="is_active" value="1" class="w-4 h-4 rounded accent-emerald-500">
                    <span class="text-sm text-gray-600">DU/DI Aktif</span>
                </label>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
                            class="flex-1 py-2.5 bg-white border border-gray-200 text-gray-600 text-sm rounded-xl">Batal</button>
                    <button type="submit" class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl">Simpan</button>
                </div>
            </form>
        </div>
        </div>
    </div>

    <script>
        const TEACHERS = @json($teachers->map(fn($t) => ['id' => $t->id, 'name' => $t->name]));

        function openEditLoc(id, data, supervisorIds) {
            const form = document.getElementById('form-edit-loc');
            form.action = '/admin/prakerin/locations/' + id;
            form.querySelector('[name="name"]').value                    = data.name || '';
            form.querySelector('[name="address"]').value                 = data.address || '';
            form.querySelector('[name="latitude"]').value                = data.latitude || '';
            form.querySelector('[name="longitude"]').value               = data.longitude || '';
            form.querySelector('[name="radius_meters"]').value           = data.radius_meters || 300;
            form.querySelector('[name="field_supervisor_name"]').value   = data.field_supervisor_name || '';
            form.querySelector('[name="field_supervisor_phone"]').value  = data.field_supervisor_phone || '';
            form.querySelector('[name="checkin_time"]').value            = data.checkin_time || '';
            form.querySelector('[name="checkout_time"]').value           = data.checkout_time || '';
            form.querySelector('[name="checkin_late_after"]').value      = data.checkin_late_after || '';
            document.getElementById('edit-is-active').checked           = data.is_active == 1;

            // Guru pembimbing checkboxes
            form.querySelectorAll('[name="teacher_ids[]"]').forEach(cb => {
                cb.checked = supervisorIds.includes(parseInt(cb.value));
            });

            document.getElementById('modal-edit').classList.remove('hidden');
        }

        ['modal-tambah','modal-edit'].forEach(id => {
            document.getElementById(id).addEventListener('click', function(e) {
                // Tutup jika klik tepat di overlay (bukan di dalam card)
                if (e.target === this || e.target.classList.contains('min-h-full')) {
                    this.classList.add('hidden');
                }
            });
        });

        function detectGps(latId, lngId, statusId) {
            const status = document.getElementById(statusId);
            status.textContent = 'Mendeteksi...';
            navigator.geolocation.getCurrentPosition(
                pos => {
                    document.getElementById(latId).value = pos.coords.latitude.toFixed(8);
                    document.getElementById(lngId).value = pos.coords.longitude.toFixed(8);
                    status.textContent = '✓ Lokasi terdeteksi (±' + Math.round(pos.coords.accuracy) + 'm)';
                    status.className = 'text-blue-600 text-xs';
                },
                () => { status.textContent = 'Gagal. Isi manual.'; status.className = 'text-red-600 text-xs'; },
                { enableHighAccuracy: true, timeout: 15000 }
            );
        }
    </script>

</x-simans-layout>