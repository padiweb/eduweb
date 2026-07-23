<x-simans-layout title="Kelola QR Kelas">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Kelola QR Kelas</h1>
        <p class="text-gray-500 text-sm mt-1">
            Perbarui QR token kelas jika QR bermasalah atau perlu diganti.
            QR permanen di papan kelas tidak perlu dicetak ulang setelah diperbarui.
        </p>
    </div>

    {{-- Info penting --}}
    <div class="bg-amber-900/20 border border-amber-200 rounded-xl px-4 py-3 mb-6 flex items-start gap-3">
        <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
        </svg>
        <div class="text-sm text-amber-700">
            <p class="font-semibold mb-0.5">Catatan Penting</p>
            <p class="text-amber-600/80 text-xs">
                QR yang ditempel di papan kelas berisi URL permanen (<code class="bg-amber-900/40 px-1 rounded">/absensi/kelas/{slug}</code>)
                yang tidak pernah berubah. Perbarui token di sini hanya mempengaruhi token internal —
                siswa tetap bisa scan QR yang sama dari papan kelas.
            </p>
        </div>
    </div>

    {{-- Grid kelas --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($classrooms as $classroom)
            @php
                $session = $todaySessions->get($classroom->id);
            @endphp

            <div class="tbl-card" id="card-{{ $classroom->id }}">

                {{-- Header --}}
                <div class="flex items-start justify-between p-5 pb-4">
                    <div>
                        <h3 class="font-bold text-gray-900">{{ $classroom->name }}</h3>
                        <p class="text-gray-500 text-sm">{{ $classroom->major->name }}</p>
                        <p class="text-gray-500 text-xs mt-0.5">{{ $classroom->students->count() }} siswa</p>
                    </div>
                    @if($session)
                        <span class="flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full
                            {{ $session->is_closed
                                ? 'bg-white text-gray-500 border border-gray-200'
                                : 'bg-blue-50 text-blue-600 border border-blue-200' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $session->is_closed ? 'bg-gray-500' : 'bg-blue-500 animate-pulse' }}"></span>
                            {{ $session->is_closed ? 'Ditutup' : 'Aktif' }}
                        </span>
                    @else
                        <span class="text-xs text-gray-500 bg-white border border-gray-200 px-2.5 py-1 rounded-full">
                            Belum ada sesi
                        </span>
                    @endif
                </div>

                {{-- Info sesi --}}
                @if($session)
                    <div class="px-5 mb-4">
                        <p class="text-xs text-gray-500">
                            QR terakhir diperbarui:
                            <span class="text-gray-600 font-medium" id="qr-time-{{ $classroom->id }}">
                                {{ $session->qr_generated_at ? $session->qr_generated_at->format('H:i:s') : '-' }}
                            </span>
                        </p>
                    </div>
                @endif

                {{-- Aksi --}}
                <div class="px-5 pb-5 flex gap-2">

                    {{-- Cetak QR permanen --}}
                    <a href="{{ route('admin.qr.cetak', $classroom) }}"
                       target="_blank"
                       class="flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-xl bg-white hover:bg-gray-50 text-gray-500 hover:text-blue-600 border border-gray-200 transition-colors"
                       title="Cetak QR Permanen">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/>
                        </svg>
                        Cetak QR
                    </a>

                    {{-- Perbarui QR token — hanya admin --}}
                    {{-- Selalu tampil, jika sesi belum ada akan dibuat otomatis --}}
                    <button type="button"
                            onclick="refreshQr({{ $classroom->id }}, '{{ $classroom->name }}')"
                            class="flex-1 flex items-center justify-center gap-2 text-sm font-semibold py-2 rounded-xl bg-blue-50 hover:bg-blue-50 text-blue-600 border border-blue-200 transition-colors"
                            id="btn-refresh-{{ $classroom->id }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                        </svg>
                        Perbarui QR
                    </button>
                </div>

                {{-- Preview QR baru setelah diperbarui --}}
                <div id="qr-preview-{{ $classroom->id }}" class="hidden px-5 pb-5">
                    <div class="bg-white rounded-xl p-3 w-fit mx-auto">
                        <div class="w-40 h-40 flex items-center justify-center" id="qr-img-{{ $classroom->id }}"></div>
                    </div>
                    <p class="text-center text-xs text-blue-600 mt-2">QR berhasil diperbarui</p>
                </div>
            </div>
        @empty
            <div class="col-span-3 bg-white border border-gray-200 rounded-xl p-12 text-center">
                <p class="text-gray-500">Belum ada kelas aktif.</p>
            </div>
        @endforelse
    </div>

    <script>
    (function() {
        var CSRF = document.querySelector('meta[name="csrf-token"]').content;

        window.refreshQr = async function(classroomId, classroomName) {
            if (! confirm('Perbarui QR untuk kelas ' + classroomName + '?\n\nSiswa yang sedang membuka halaman absensi perlu reload.')) {
                return;
            }

            var btn = document.getElementById('btn-refresh-' + classroomId);
            if (btn) { btn.disabled = true; btn.textContent = 'Memperbarui...'; }

            try {
                var res  = await fetch('/admin/qr/' + classroomId + '/refresh', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                });
                var data = await res.json();

                if (data.success) {
                    // Update waktu
                    var timeEl = document.getElementById('qr-time-' + classroomId);
                    if (timeEl) timeEl.textContent = data.qr_generated_at;

                    // Tampilkan preview QR baru
                    var preview = document.getElementById('qr-preview-' + classroomId);
                    var imgEl   = document.getElementById('qr-img-' + classroomId);
                    if (preview && imgEl) {
                        imgEl.innerHTML = atob(data.qr_image);
                        preview.classList.remove('hidden');
                    }

                    if (btn) {
                        btn.disabled     = false;
                        btn.innerHTML    = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg> Perbarui QR';
                    }
                } else {
                    alert(data.message || 'Gagal memperbarui QR.');
                    if (btn) { btn.disabled = false; btn.textContent = 'Perbarui QR'; }
                }
            } catch(e) {
                alert('Koneksi gagal. Coba lagi.');
                if (btn) { btn.disabled = false; btn.textContent = 'Perbarui QR'; }
            }
        };
    })();
    </script>

</x-simans-layout>