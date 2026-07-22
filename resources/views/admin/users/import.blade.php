<x-simans-layout title="Import User">
    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}" class="text-gray-500 text-sm hover:text-blue-600 flex items-center gap-1 mb-3 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Import User dari CSV</h1>
        <p class="text-gray-500 text-sm mt-1">Upload file CSV untuk menambahkan banyak user sekaligus</p>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-600 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-sm">{{ session('error') }}</div>
    @endif
    @if(session('import_errors') && count(session('import_errors')) > 0)
        <div class="mb-4 p-3 rounded-xl bg-amber-500/5 border border-amber-200">
            <p class="text-amber-600 text-sm font-semibold mb-2">Beberapa baris dilewati:</p>
            @foreach(session('import_errors') as $err)
                <p class="text-amber-600/70 text-xs">{{ $err }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Form upload --}}
        <div class="bg-white border border-gray-200 rounded-xl p-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Upload File</h2>
            <form action="{{ route('admin.users.import.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs text-gray-500 font-medium mb-1.5">Role <span class="text-red-600">*</span></label>
                        <select name="role" id="role-select" onchange="updateTemplate()"
                                class="w-full bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-300">
                            <option value="siswa" {{ request('role') === 'siswa' ? 'selected' : '' }}>Siswa</option>
                            <option value="guru" {{ request('role') === 'guru' ? 'selected' : '' }}>Guru</option>
                            <option value="wali_kelas" {{ request('role') === 'wali_kelas' ? 'selected' : '' }}>Wali Kelas</option>
                            <option value="kesiswaan">Kesiswaan</option>
                            <option value="bendahara">Bendahara</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div id="kelas-field">
                        <label class="block text-xs text-gray-500 font-medium mb-1.5">Masukkan ke Kelas <span class="text-gray-500">(opsional, untuk siswa)</span></label>
                        <select name="kelas_id"
                                class="w-full bg-white border border-gray-200 text-gray-600 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-300">
                            <option value="">— Tidak ditambahkan ke kelas —</option>
                            @foreach($classrooms as $cls)
                                <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 font-medium mb-1.5">File CSV <span class="text-red-600">*</span></label>
                        <input type="file" name="file" accept=".csv"
                               class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-700 file:text-white hover:file:bg-blue-600 cursor-pointer"/>
                        <p class="text-xs text-gray-500 mt-1">Hanya file .csv, maksimal 5MB</p>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                                class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                            Import Sekarang
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Panduan --}}
        <div class="space-y-4">
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Download Template</h2>
                <p class="text-gray-500 text-xs mb-3">Download template CSV sesuai role, isi data, lalu upload.</p>
                <div class="flex gap-2 flex-wrap">
                    <a id="template-link" href="{{ route('admin.users.import.template', ['role' => 'siswa']) }}"
                       class="flex items-center gap-2 px-3 py-2 bg-blue-50 border border-blue-200 text-blue-400 text-xs rounded-xl hover:bg-blue-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        Download Template
                    </a>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Format CSV Siswa</h2>
                <div class="bg-white rounded-xl p-3 font-mono text-xs text-gray-600 overflow-x-auto">
                    <p class="text-gray-500 mb-1"># Baris pertama adalah header (wajib)</p>
                    <p class="text-blue-600">name,nis,nisn,email,phone</p>
                    <p>Andika Wicaksono,123456,1234567890,,</p>
                    <p>Ardi Nugroho,123457,,,</p>
                </div>
                <ul class="mt-3 space-y-1 text-xs text-gray-500">
                    <li>• <span class="text-gray-600">name</span> — Nama lengkap (wajib)</li>
                    <li>• <span class="text-gray-600">nis</span> — NIS siswa (jika kosong, NIS tidak diset)</li>
                    <li>• <span class="text-gray-600">email</span> — Jika kosong, dibuat otomatis</li>
                    <li>• Password default = NIS (atau nama jika NIS kosong)</li>
                    <li>• Siswa dengan NIS yang sudah ada akan dilewati</li>
                </ul>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Format CSV Guru</h2>
                <div class="bg-white rounded-xl p-3 font-mono text-xs text-gray-600 overflow-x-auto">
                    <p class="text-blue-600">name,nip,email,phone</p>
                    <p>Dewi Kusuma S.Pd,197501012005012001,,</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateTemplate() {
            const role = document.getElementById('role-select').value;
            document.getElementById('template-link').href = '{{ route("admin.users.import.template") }}?role=' + role;
            document.getElementById('kelas-field').style.display = role === 'siswa' ? 'block' : 'none';
        }
        updateTemplate();
    </script>
</x-simans-layout>
