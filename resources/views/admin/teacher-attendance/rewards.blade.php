<x-simans-layout title="Rekap Poin Reward Guru">

    <div class="mb-6">
        <a href="{{ route('admin.teacher-attendance.index') }}"
           class="flex items-center gap-1 text-gray-500 hover:text-gray-900 text-sm mb-2 transition-colors w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
            </svg>
            Kembali
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Rekap Poin Reward Guru</h1>
        <p class="text-gray-500 text-sm mt-1">Data dapat digunakan sebagai acuan reward/salary</p>
    </div>

    {{-- Filter bulan --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 mb-6">
        <form method="GET" class="flex items-center gap-3 flex-wrap">
            <label class="text-sm text-gray-500">Periode:</label>
            <select name="month"
                    class="bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
            <select name="year"
                    class="bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                @foreach(range(now()->year, now()->year - 3) as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-gray-900 text-sm px-4 py-2 rounded-xl transition-colors">
                Lihat
            </button>
        </form>
    </div>

    {{-- Form tambah poin manual --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5 mb-6" x-data="{ show: false }">
        <button type="button" @click="show=!show"
                class="text-sm text-gray-500 hover:text-gray-900 transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah / Kurang Poin Manual
        </button>
        <div x-show="show" x-cloak class="mt-4">
            <form method="POST" action="{{ route('admin.teacher-attendance.add-reward') }}"
                  class="flex flex-wrap items-end gap-3">
                @csrf
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Guru</label>
                    <select name="teacher_id" required
                            class="bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                        <option value="">Pilih guru...</option>
                        @foreach($teachers as $item)
                            <option value="{{ $item['teacher']->id }}">{{ $item['teacher']->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Tipe</label>
                    <select name="type" required
                            class="bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                        <option value="bonus">Bonus</option>
                        <option value="pengurang">Pengurang</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Poin</label>
                    <input type="number" name="points" min="1" max="100" required
                           class="w-20 bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                </div>
                <div class="flex-1 min-w-48">
                    <label class="block text-xs text-gray-400 mb-1">Keterangan</label>
                    <input type="text" name="description" required placeholder="cth: Lomba guru berprestasi"
                           class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                </div>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-gray-900 text-sm px-4 py-2 rounded-xl transition-colors">
                    Simpan
                </button>
            </form>
        </div>
    </div>

    {{-- Tabel rekap --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900">
                {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} {{ $year }}
            </h2>
            <span class="text-xs text-gray-400">{{ $teachers->count() }} guru</span>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($teachers as $item)
                <div class="flex items-center gap-3 px-5 py-4">
                    <div style="width:36px;height:36px;border-radius:50%;overflow:hidden;flex-shrink:0">
                        <img src="{{ $item['teacher']->avatarUrl }}" class="w-full h-full object-cover" alt="">
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900">{{ $item['teacher']->name }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            <span class="text-blue-600">{{ $item['absen_tepat_waktu'] }} absen</span>
                            &middot;
                            <span class="text-blue-400">{{ $item['isi_jurnal'] }} jurnal</span>
                            @if($item['bonus'] > 0) &middot; <span class="text-amber-400">+{{ $item['bonus'] }} bonus</span> @endif
                            @if($item['pengurang'] < 0) &middot; <span class="text-red-400">{{ $item['pengurang'] }} pengurang</span> @endif
                        </p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-lg font-bold {{ $item['total'] > 0 ? 'text-blue-600' : 'text-gray-400' }}">
                            {{ $item['total'] }}
                        </p>
                        <p class="text-xs text-gray-400">poin</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</x-simans-layout>
