<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BankSettingController extends Controller
{
    public function index()
    {
        $school = auth()->user()->school;
        return view('bendahara.settings.bank', compact('school'));
    }

    public function update(Request $request)
    {
        $school = auth()->user()->school;

        $validated = $request->validate([
            'bank_name'           => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_name'   => ['nullable', 'string', 'max:100'],
            'bank_logo'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:1024'],
            'payment_instructions'=> ['nullable', 'string', 'max:1000'],
        ]);

        if ($request->hasFile('bank_logo')) {
            if ($school->bank_logo_path && Storage::disk('public')->exists($school->bank_logo_path)) {
                Storage::disk('public')->delete($school->bank_logo_path);
            }
            $validated['bank_logo_path'] = $request->file('bank_logo')
                ->store('bank-logos', 'public');
        }

        // Hapus 'bank_logo' dari validated (bukan field DB)
        unset($validated['bank_logo']);

        $school->update($validated);

        // Clear cache sekolah
        cache()->forget("school_u{$school->id}");

        return back()->with('success', 'Informasi rekening bank berhasil disimpan.');
    }

    public function deleteLogo()
    {
        $school = auth()->user()->school;
        if ($school->bank_logo_path && Storage::disk('public')->exists($school->bank_logo_path)) {
            Storage::disk('public')->delete($school->bank_logo_path);
        }
        $school->update(['bank_logo_path' => null]);
        cache()->forget("school_u{$school->id}");
        return back()->with('success', 'Logo bank dihapus.');
    }
}
