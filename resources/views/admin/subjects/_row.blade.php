<div class="flex items-center gap-4 px-5 py-3.5" x-data="{ editing: false }">
    {{-- Info mapel --}}
    <div class="flex-1 min-w-0" x-show="!editing">
        <p class="text-sm font-semibold text-gray-900">{{ $subject->name }}</p>
        <p class="text-xs text-gray-400 mt-0.5">
            {{ $subject->code ? 'Kode: '.$subject->code.' &middot; ' : '' }}
            {{ $subject->major?->name ?? 'Semua jurusan' }}
            &middot; {{ $subject->schedules_count }} jadwal
        </p>
    </div>

    {{-- Form edit inline --}}
    <form method="POST" action="{{ route('admin.subjects.update', $subject->id) }}"
          class="flex-1 space-y-2" x-show="editing" x-cloak>
        @csrf @method('PUT')
        <div class="grid grid-cols-2 gap-2">
            <input type="text" name="name" value="{{ $subject->name }}" required
                   class="bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
            <input type="text" name="code" value="{{ $subject->code }}" placeholder="Kode"
                   class="bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
        </div>
        <div class="grid grid-cols-2 gap-2">
            <select name="subject_group_id"
                    class="bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                <option value="">Tanpa kelompok</option>
                @foreach($groups->sortBy('sort_order') as $g)
                    <option value="{{ $g->id }}" {{ $subject->subject_group_id == $g->id ? 'selected' : '' }}>
                        {{ $g->code ? '['.$g->code.'] ' : '' }}{{ $g->name }}
                    </option>
                @endforeach
            </select>
            <select name="major_id"
                    class="bg-white border border-gray-200 text-gray-700 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                <option value="">Semua jurusan</option>
                @foreach($majors as $m)
                    <option value="{{ $m->id }}" {{ $subject->major_id == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 text-xs text-blue-600 bg-blue-600/10 border border-blue-200 hover:bg-blue-600/20 py-1.5 rounded-lg transition-colors">Simpan</button>
            <button type="button" @click="editing=false" class="flex-1 text-xs text-gray-500 bg-white border border-gray-200 py-1.5 rounded-lg transition-colors">Batal</button>
        </div>
    </form>

    {{-- Aksi --}}
    <div class="flex items-center gap-1 flex-shrink-0" x-show="!editing">
        <button type="button" @click="editing=true"
                class="w-7 h-7 flex items-center justify-center rounded-lg bg-white hover:bg-gray-100 border border-gray-200 text-gray-500 hover:text-gray-900 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
            </svg>
        </button>
        <form method="POST" action="{{ route('admin.subjects.destroy', $subject->id) }}"
              onsubmit="return confirm('Hapus mapel {{ addslashes($subject->name) }}?')">
            @csrf @method('DELETE')
            <button class="w-7 h-7 flex items-center justify-center rounded-lg bg-white hover:bg-red-900/40 border border-gray-200 hover:border-red-500/30 text-gray-500 hover:text-red-400 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                </svg>
            </button>
        </form>
    </div>
</div>
