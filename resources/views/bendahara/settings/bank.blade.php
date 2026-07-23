<x-simans-layout title="Rekening Pembayaran">

<div style="max-width:680px">

    {{-- Header --}}
    <div style="margin-bottom:24px">
        <h1 style="font-size:22px;font-weight:800;color:#0f172a;margin:0 0 4px">Rekening Pembayaran</h1>
        <p style="font-size:13px;color:#64748b;margin:0">Informasi ini tampil di halaman pembayaran siswa saat hendak transfer</p>
    </div>

    @if(session('success'))
        <div style="display:flex;align-items:center;gap:10px;background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:12px 16px;border-radius:12px;font-size:13.5px;font-weight:500;margin-bottom:20px">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('bendahara.settings.bank.update') }}" enctype="multipart/form-data">
        @csrf

        {{-- Preview rekening aktif --}}
        @if($school->bank_name || $school->bank_account_number)
        <div style="background:linear-gradient(135deg,#fffbeb,#fef3c7);border:1.5px solid #fde68a;border-radius:14px;padding:16px 20px;margin-bottom:20px">
            <p style="font-size:11px;font-weight:700;color:#b45309;text-transform:uppercase;letter-spacing:.06em;margin:0 0 10px">Preview — Tampil di Halaman Siswa</p>
            <div style="background:#fff;border-radius:10px;padding:14px 16px;display:flex;flex-direction:column;gap:8px">
                @if($school->bank_logo_path)
                    <img src="{{ Storage::url($school->bank_logo_path) }}" alt="Logo Bank"
                         style="height:28px;object-fit:contain;margin-bottom:4px">
                @endif
                @if($school->bank_name)
                    <div style="display:flex;justify-content:space-between">
                        <span style="font-size:12px;color:#64748b">Bank</span>
                        <span style="font-size:12px;font-weight:700;color:#1e293b">{{ $school->bank_name }}</span>
                    </div>
                @endif
                @if($school->bank_account_number)
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <span style="font-size:12px;color:#64748b">No. Rekening</span>
                        <span style="font-size:15px;font-weight:800;color:#1d4ed8;letter-spacing:1px">{{ $school->bank_account_number }}</span>
                    </div>
                @endif
                @if($school->bank_account_name)
                    <div style="display:flex;justify-content:space-between">
                        <span style="font-size:12px;color:#64748b">Atas Nama</span>
                        <span style="font-size:12px;font-weight:600;color:#1e293b">{{ $school->bank_account_name }}</span>
                    </div>
                @endif
            </div>
            @if($school->payment_instructions)
                <p style="font-size:11.5px;color:#b45309;margin:10px 0 0;line-height:1.6">
                    📋 {{ $school->payment_instructions }}
                </p>
            @endif
        </div>
        @endif

        {{-- Form --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;box-shadow:0 1px 4px rgba(15,23,42,.06)">

            <h2 style="font-size:14px;font-weight:700;color:#0f172a;margin:0 0 20px;display:flex;align-items:center;gap:8px">
                <div style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#f59e0b,#d97706);display:flex;align-items:center;justify-content:center">
                    <svg width="14" height="14" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                    </svg>
                </div>
                Data Rekening
            </h2>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px" class="bank-grid">
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:6px">
                        Nama Bank <span style="color:#ef4444">*</span>
                    </label>
                    <select name="bank_name"
                            style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 12px;font-size:13px;color:#334155;background:#fff;outline:none"
                            onchange="handleBankSelect(this)">
                        <option value="">-- Pilih Bank --</option>
                        @foreach(['BCA','BRI','BNI','Mandiri','BSI (Bank Syariah Indonesia)','CIMB Niaga','Danamon','Permata','BTN','BPD Jateng','BPD Jatim','BPD Jabar','BPD DIY','Muamalat','BRI Syariah','Lainnya'] as $bank)
                            <option value="{{ $bank }}" {{ $school->bank_name === $bank ? 'selected' : '' }}>{{ $bank }}</option>
                        @endforeach
                    </select>
                    {{-- Input manual jika pilih Lainnya --}}
                    <input type="text" id="bank-name-custom" name="bank_name_custom"
                           value="{{ !in_array($school->bank_name, ['BCA','BRI','BNI','Mandiri','BSI (Bank Syariah Indonesia)','CIMB Niaga','Danamon','Permata','BTN','BPD Jateng','BPD Jatim','BPD Jabar','BPD DIY','Muamalat','BRI Syariah','Lainnya']) && $school->bank_name ? $school->bank_name : '' }}"
                           placeholder="Nama bank lainnya..."
                           style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 12px;font-size:13px;color:#334155;outline:none;margin-top:8px;{{ !in_array($school->bank_name, ['BCA','BRI','BNI','Mandiri','BSI (Bank Syariah Indonesia)','CIMB Niaga','Danamon','Permata','BTN','BPD Jateng','BPD Jatim','BPD Jabar','BPD DIY','Muamalat','BRI Syariah']) && $school->bank_name ? '' : 'display:none' }}">
                    @error('bank_name')
                        <p style="font-size:11px;color:#dc2626;margin-top:4px">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:6px">
                        Nomor Rekening <span style="color:#ef4444">*</span>
                    </label>
                    <input type="text" name="bank_account_number"
                           value="{{ $school->bank_account_number }}"
                           placeholder="Contoh: 1234567890"
                           style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 12px;font-size:13px;color:#334155;outline:none;letter-spacing:1px">
                    @error('bank_account_number')
                        <p style="font-size:11px;color:#dc2626;margin-top:4px">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:6px">Atas Nama</label>
                    <input type="text" name="bank_account_name"
                           value="{{ $school->bank_account_name }}"
                           placeholder="Nama pemilik rekening"
                           style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 12px;font-size:13px;color:#334155;outline:none">
                </div>

                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:6px">
                        Logo Bank
                        <span style="font-weight:400;color:#94a3b8">(PNG/JPG, maks. 1MB)</span>
                    </label>
                    @if($school->bank_logo_path)
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
                            <img src="{{ Storage::url($school->bank_logo_path) }}"
                                 alt="Logo Bank" style="height:32px;object-fit:contain;border-radius:4px;border:1px solid #e2e8f0;padding:4px">
                            <form method="POST" action="{{ route('bendahara.settings.bank.logo.delete') }}" style="display:inline">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('Hapus logo bank?')"
                                        style="font-size:11px;color:#dc2626;background:#fff1f2;border:1px solid #fecaca;border-radius:6px;padding:4px 8px;cursor:pointer">
                                    Hapus Logo
                                </button>
                            </form>
                        </div>
                    @endif
                    <input type="file" name="bank_logo" accept="image/*"
                           style="width:100%;background:#fff;border:1.5px solid #e2e8f0;color:#475569;border-radius:10px;padding:8px 12px;font-size:13px;file:mr-3 file:text-xs file:bg-blue-600 file:text-white file:border-0 file:rounded file:px-2 file:py-1">
                </div>
            </div>

            <div>
                <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:6px">
                    Instruksi Transfer
                    <span style="font-weight:400;color:#94a3b8">(tampil di halaman pembayaran)</span>
                </label>
                <textarea name="payment_instructions" rows="3"
                          placeholder="Contoh: Transfer ke rekening di atas sesuai nominal tagihan. Cantumkan nama siswa &amp; kelas di keterangan transfer, lalu upload bukti di aplikasi."
                          style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 12px;font-size:13px;color:#334155;outline:none;resize:vertical;line-height:1.5">{{ $school->payment_instructions }}</textarea>
            </div>

        </div>

        {{-- Tombol simpan --}}
        <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px">
            <a href="{{ route('bendahara.dashboard') }}"
               style="padding:10px 20px;background:#fff;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;color:#64748b;text-decoration:none">
                Batal
            </a>
            <button type="submit"
                    style="padding:10px 24px;background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;box-shadow:0 2px 8px rgba(59,130,246,0.30)">
                Simpan Rekening
            </button>
        </div>
    </form>
</div>

<style>
@media(max-width:639px){
    .bank-grid{grid-template-columns:1fr!important}
}
</style>

<script>
function handleBankSelect(sel) {
    var custom = document.getElementById('bank-name-custom');
    if (sel.value === 'Lainnya') {
        custom.style.display = 'block';
        custom.required = true;
    } else {
        custom.style.display = 'none';
        custom.required = false;
        custom.value = '';
    }
}
// Saat submit: jika pilih Lainnya, pakai nilai custom
document.querySelector('form').addEventListener('submit', function(e) {
    var sel = document.querySelector('select[name="bank_name"]');
    var custom = document.getElementById('bank-name-custom');
    if (sel.value === 'Lainnya' && custom.value.trim()) {
        sel.value = custom.value.trim();
    }
});
</script>
</x-simans-layout>
