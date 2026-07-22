<x-simans-layout title="Penggajian">

    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900">Penggajian Guru & Karyawan</h1>
        <p class="text-gray-500 text-sm mt-0.5">Kelola gaji pokok, tunjangan JP, dan tunjangan jabatan</p>
    </div>

    {{-- Coming soon card --}}
    <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
        <div class="w-16 h-16 bg-green-500/10 border border-green-500/20 rounded-xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
            </svg>
        </div>
        <h2 class="text-gray-900 font-semibold text-lg mb-2">Modul Penggajian</h2>
        <p class="text-gray-500 text-sm max-w-md mx-auto mb-6">
            Modul ini sedang dalam pengembangan. Akan mencakup gaji pokok, tunjangan JP (per jam pelajaran),
            tunjangan jabatan (wali kelas, ketua PPDB, dll), potongan, dan slip gaji digital.
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 max-w-lg mx-auto text-left">
            @foreach([
                ['icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => 'Gaji Pokok', 'desc' => 'Per guru/karyawan'],
                ['icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => 'Tunjangan JP', 'desc' => 'Jumlah JP × tarif'],
                ['icon' => 'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342', 'label' => 'Tunjangan Jabatan', 'desc' => 'Wali kelas, PPDB, dll'],
            ] as $f)
            <div class="bg-white rounded-xl p-4">
                <svg class="w-5 h-5 text-green-400 mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $f['icon'] }}"/>
                </svg>
                <p class="text-sm font-medium text-gray-900">{{ $f['label'] }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ $f['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

</x-simans-layout>
