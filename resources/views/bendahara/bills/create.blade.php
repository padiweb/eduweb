<x-simans-layout title="Buat Tagihan">

    <div class="mb-6">
        <a href="{{ route('bendahara.bills.index') }}" class="text-gray-400 hover:text-white text-sm flex items-center gap-1 mb-3 w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Kembali
        </a>
        <h1 class="text-xl font-bold text-white">Buat Tagihan Baru</h1>
        <p class="text-gray-400 text-sm mt-0.5">Buat tagihan untuk satu kelas atau siswa tertentu</p>
    </div>

    @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-sm rounded-lg px-4 py-3 mb-4">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('bendahara.bills.store') }}" x-data="billForm()">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            {{-- Kiri --}}
            <div class="space-y-4">
                <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-white mb-4">Informasi Tagihan</h2>
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Jenis Pembayaran *</label>
                            <select name="payment_type_id" required
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                                <option value="">-- Pilih jenis --</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Tahun Ajaran *</label>
                            <select name="academic_year_id" required
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>
                                        {{ $year->name }} Sem {{ $year->semester }}{{ $year->is_active ? ' (Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-gray-400 mb-1 block">Label Periode *</label>
                                <input type="text" name="period_label" required x-model="periodLabel"
                                    placeholder="Juli 2026"
                                    class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="text-xs text-gray-400 mb-1 block">Tanggal Periode *</label>
                                <input type="date" name="period_date" required value="{{ date('Y-m-01') }}"
                                    class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Jatuh Tempo</label>
                            <input type="date" name="due_date"
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-white mb-4">Skema Pembayaran</h2>
                    <div class="space-y-3">
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="installment_type" value="full" x-model="installType" class="text-purple-500">
                                <span class="text-sm text-white">Bayar penuh</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="installment_type" value="installment" x-model="installType" class="text-purple-500">
                                <span class="text-sm text-white">Cicilan</span>
                            </label>
                        </div>
                        <div x-show="installType === 'installment'" x-cloak>
                            <label class="text-xs text-gray-400 mb-1 block">Jumlah Cicilan</label>
                            <select name="installment_count"
                                class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                                @for($i = 2; $i <= 12; $i++)
                                    <option value="{{ $i }}">{{ $i }} cicilan</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kanan --}}
            <div class="bg-gray-900 border border-white/5 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-white mb-4">Target Siswa</h2>
                <div class="flex gap-4 mb-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="scope" value="classroom" x-model="scope" class="text-purple-500">
                        <span class="text-sm text-white">Per kelas</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="scope" value="student" x-model="scope" class="text-purple-500">
                        <span class="text-sm text-white">Pilih siswa</span>
                    </label>
                </div>

                <div x-show="scope === 'classroom'" x-cloak>
                    <label class="text-xs text-gray-400 mb-1 block">Kelas *</label>
                    <select name="classroom_id"
                        class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none">
                        <option value="">-- Pilih kelas --</option>
                        @foreach($classrooms as $cls)
                            <option value="{{ $cls->id }}">
                                {{ $cls->name }}{{ $cls->major ? ' - ' . $cls->major->name : '' }}
                                ({{ $cls->students->count() }} siswa)
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-2">Tagihan dibuat untuk semua siswa aktif di kelas ini</p>
                </div>

                <div x-show="scope === 'student'" x-cloak>
                    <input type="text" x-model="search" placeholder="Cari nama atau NIS..."
                        class="w-full bg-gray-800 border border-white/10 text-white text-sm rounded-lg px-3 py-2 focus:border-purple-500 focus:outline-none mb-2">
                    <div class="max-h-72 overflow-y-auto space-y-1 border border-white/5 rounded-lg p-2">
                        @foreach($classrooms as $cls)
                            @if($cls->students->isNotEmpty())
                                <p class="text-xs text-gray-500 px-2 py-1 font-medium">{{ $cls->name }}</p>
                                @foreach($cls->students as $student)
                                <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-800 cursor-pointer"
                                    x-show="!search || '{{ strtolower($student->name) }}'.includes(search.toLowerCase()) || '{{ $student->nis ?? '' }}'.includes(search)">
                                    <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="text-purple-500 rounded">
                                    <span class="text-sm text-white">{{ $student->name }}</span>
                                    <span class="text-xs text-gray-500 ml-auto">{{ $student->nis ?? '-' }}</span>
                                </label>
                                @endforeach
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-3 mt-5">
            <a href="{{ route('bendahara.bills.index') }}"
                class="px-6 py-2.5 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium rounded-lg transition-colors">Batal</a>
            <button type="submit"
                class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
                Buat Tagihan
            </button>
        </div>
    </form>

    <script>
    function billForm() {
        return {
            installType: 'full',
            scope: 'classroom',
            search: '',
            periodLabel: '{{ now()->translatedFormat('F Y') }}',
        }
    }
    </script>

</x-simans-layout>