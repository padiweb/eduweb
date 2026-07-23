<x-simans-layout title="Buat Tagihan">

    <div class="mb-6">
        <a href="{{ route('bendahara.bills.index') }}" class="text-gray-500 hover:text-blue-600 text-sm flex items-center gap-1 mb-3 w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Kembali
        </a>
        <h1 class="text-xl font-bold text-gray-900">Buat Tagihan Baru</h1>
        <p class="text-gray-500 text-sm mt-0.5">Tagihan dibuat berdasarkan tarif kelas, override per siswa, atau input manual</p>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-600 text-sm rounded-lg px-4 py-3 mb-4">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('bendahara.bills.store') }}" id="form-tagihan"
        x-data="billForm()" @submit.prevent="submitForm">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            {{-- Kiri --}}
            <div class="space-y-4">
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-4">Informasi Tagihan</h2>
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Jenis Pembayaran *</label>
                            <select name="payment_type_id" required x-model="typeId" @change="rateChecked = false"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                                <option value="">-- Pilih jenis --</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @if($types->isEmpty())
                                <p class="text-xs text-amber-600 mt-1">
                                    ⚠ Belum ada jenis pembayaran.
                                    <a href="{{ route('bendahara.payment-types.index') }}" class="underline">Tambah dulu →</a>
                                </p>
                            @endif
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Tahun Ajaran *</label>
                            <select name="academic_year_id" required x-model="yearId" @change="rateChecked = false"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>
                                        {{ $year->name }} Sem {{ $year->semester }}{{ $year->is_active ? ' ✓' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-gray-500 mb-1 block">Label Periode *</label>
                                <input type="text" name="period_label" required x-model="periodLabel"
                                    placeholder="Juli 2026"
                                    class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="text-xs text-gray-500 mb-1 block">Tanggal Periode *</label>
                                <input type="date" name="period_date" required value="{{ date('Y-m-01') }}"
                                    class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Jatuh Tempo</label>
                            <input type="date" name="due_date"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-4">Skema Pembayaran</h2>
                    <div class="space-y-3">
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="installment_type" value="full" x-model="installType" class="text-blue-600">
                                <span class="text-sm text-gray-900">Bayar penuh</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="installment_type" value="installment" x-model="installType" class="text-blue-600">
                                <span class="text-sm text-gray-900">Cicilan</span>
                            </label>
                        </div>
                        <div x-show="installType === 'installment'" x-cloak>
                            <label class="text-xs text-gray-500 mb-1 block">Jumlah Cicilan</label>
                            <select name="installment_count"
                                class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                                @for($i = 2; $i <= 12; $i++)
                                    <option value="{{ $i }}">{{ $i }} cicilan</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Info tarif --}}
                <div class="bg-blue-500/5 border border-blue-200 rounded-xl p-4 text-xs text-blue-300">
                    <p class="font-medium mb-1">Urutan penentuan tarif:</p>
                    <ol class="space-y-0.5 text-blue-400/80 list-decimal list-inside">
                        <li>Override per siswa (jika ada)</li>
                        <li>Tarif kelas / jurusan</li>
                        <li>Input manual (jika tidak ada keduanya)</li>
                    </ol>
                    <p class="mt-2">
                        <a href="{{ route('bendahara.bills.overrides') }}" class="underline text-blue-400 hover:text-blue-300">
                            Kelola tarif override per siswa →
                        </a>
                    </p>
                </div>
            </div>

            {{-- Kanan --}}
            <div class="space-y-4">
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-4">Target Siswa</h2>
                    <div class="flex gap-4 mb-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="scope" value="classroom" x-model="scope" @change="rateChecked = false" class="text-blue-600">
                            <span class="text-sm text-gray-900">Per kelas</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="scope" value="student" x-model="scope" @change="rateChecked = false" class="text-blue-600">
                            <span class="text-sm text-gray-900">Pilih siswa</span>
                        </label>
                    </div>

                    <div x-show="scope === 'classroom'" x-cloak>
                        <label class="text-xs text-gray-500 mb-1 block">Kelas *</label>
                        <select name="classroom_id" x-model="classroomId" @change="rateChecked = false"
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none">
                            <option value="">-- Pilih kelas --</option>
                            @foreach($classrooms as $cls)
                                <option value="{{ $cls->id }}">
                                    {{ $cls->name }}{{ $cls->major ? ' - ' . $cls->major->name : '' }}
                                    ({{ $cls->students->count() }} siswa)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="scope === 'student'" x-cloak>
                        <input type="text" x-model="search" placeholder="Cari nama atau NIS..."
                            class="w-full bg-white border border-gray-200 text-gray-700 text-sm rounded-lg px-3 py-2 focus:border-blue-500 focus:outline-none mb-2">
                        <div class="max-h-48 overflow-y-auto space-y-1 border border-gray-200 rounded-lg p-2">
                            @foreach($classrooms as $cls)
                                @if($cls->students->isNotEmpty())
                                    <p class="text-xs text-gray-500 px-2 py-1 font-medium">{{ $cls->name }}</p>
                                    @foreach($cls->students as $student)
                                    <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 cursor-pointer"
                                        x-show="!search || '{{ strtolower($student->name) }}'.includes(search.toLowerCase()) || '{{ $student->nis ?? '' }}'.includes(search)">
                                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                                            @change="rateChecked = false" class="text-blue-600 rounded">
                                        <span class="text-sm text-gray-900">{{ $student->name }}</span>
                                        <span class="text-xs text-gray-500 ml-auto">{{ $student->nis ?? '-' }}</span>
                                    </label>
                                    @endforeach
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Tombol cek tarif --}}
                    <button type="button" @click="checkRates()"
                        x-show="typeId && yearId"
                        class="mt-3 w-full text-sm text-blue-600 hover:text-blue-500 border border-blue-200 hover:border-blue-300 rounded-lg py-2 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                        <span x-text="checking ? 'Mengecek...' : 'Cek Tarif Siswa'"></span>
                    </button>
                </div>

                {{-- Hasil cek tarif --}}
                <div x-show="rateChecked" x-cloak>

                    {{-- Warning: ada siswa tanpa tarif --}}
                    <div x-show="noRateCount > 0" class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-3">
                        <p class="text-sm font-semibold text-amber-600 mb-1">
                            ⚠ <span x-text="noRateCount"></span> siswa belum punya tarif
                        </p>
                        <p class="text-xs text-amber-600/70 mb-3">Isi nominal manual di bawah, atau
                            <a href="{{ route('bendahara.payment-types.index') }}" class="underline" target="_blank">tambahkan tarif kelas</a>
                            terlebih dulu.
                        </p>
                        <template x-for="s in rateResults.filter(r => r.source === 'none')" :key="s.id">
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="text-xs text-gray-600 flex-1" x-text="s.name"></span>
                                <input type="number"
                                    :name="'manual_amount[' + s.id + ']'"
                                    placeholder="0"
                                    min="0"
                                    class="w-32 bg-white border border-amber-200 text-gray-900 text-xs rounded-lg px-2 py-1.5 focus:border-amber-500 focus:outline-none">
                                <span class="text-xs text-gray-500">Rp</span>
                            </div>
                        </template>
                    </div>

                    {{-- Siswa dengan tarif --}}
                    <div class="tbl-card">
                        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                            <p class="text-xs font-semibold text-gray-900">Preview Tagihan</p>
                            <p class="text-xs text-gray-500">
                                <span x-text="rateResults.length"></span> siswa total
                            </p>
                        </div>
                        <div class="divide-y divide-gray-100 max-h-56 overflow-y-auto">
                            <template x-for="s in rateResults" :key="s.id">
                                <div class="px-4 py-2.5 flex items-center justify-between">
                                    <span class="text-sm text-gray-900" x-text="s.name"></span>
                                    <div class="text-right">
                                        <span class="text-sm font-medium"
                                            :class="s.amount > 0 ? 'text-green-600' : 'text-amber-600'"
                                            x-text="s.amount > 0 ? 'Rp ' + s.amount.toLocaleString('id-ID') : 'Input manual'">
                                        </span>
                                        <p class="text-xs text-gray-500"
                                            x-text="s.source === 'override' ? 'Tarif khusus' : s.source === 'rate' ? 'Tarif kelas' : 'Belum ada tarif'">
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-3 mt-5">
            <a href="{{ route('bendahara.bills.index') }}"
                class="px-6 py-2.5 bg-white hover:bg-gray-50 text-gray-600 text-sm font-medium rounded-lg transition-colors">Batal</a>
            <button type="submit"
                class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                Buat Tagihan
            </button>
        </div>
    </form>

    @push('scripts')
    <script>
    function billForm() {
        return {
            installType:  'full',
            scope:        'classroom',
            search:       '',
            periodLabel:  '{{ now()->translatedFormat("F Y") }}',
            typeId:       '',
            yearId:       '{{ $academicYears->firstWhere("is_active", true)?->id ?? "" }}',
            classroomId:  '',
            checking:     false,
            rateChecked:  false,
            rateResults:  [],
            noRateCount:  0,

            async checkRates() {
                this.checking = true;
                const form = document.getElementById('form-tagihan');
                const fd = new FormData(form);

                try {
                    const res = await fetch('{{ route("bendahara.bills.check-rate") }}', {
                        method:  'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        },
                        body: fd,
                    });
                    const data = await res.json();
                    this.rateResults  = data.results ?? [];
                    this.noRateCount  = data.no_rate ?? 0;
                    this.rateChecked  = true;
                } catch(e) {
                    alert('Gagal mengecek tarif. Coba lagi.');
                } finally {
                    this.checking = false;
                }
            },

            async submitForm() {
                document.getElementById('form-tagihan').submit();
            }
        }
    }
    </script>
    @endpush

</x-simans-layout>
