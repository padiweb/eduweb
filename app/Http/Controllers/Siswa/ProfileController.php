<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\StudentDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    // Tampilkan halaman profil (view data)
    public function show()
    {
        $user   = auth()->user();
        $detail = $user->studentDetail;
        return view('siswa.profile.show', compact('user', 'detail'));
    }

    // Form edit profil
    public function edit()
    {
        $user   = auth()->user();
        $detail = $user->studentDetail ?? new StudentDetail(['user_id' => $user->id]);
        return view('siswa.profile.edit', compact('user', 'detail'));
    }

    // Proses simpan
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $detailData = $request->validate([
            'birth_place'     => ['nullable', 'string', 'max:100'],
            'birth_date'      => ['nullable', 'date', 'before:today'],
            'gender'          => ['nullable', 'in:L,P'],
            'religion'        => ['nullable', 'in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu'],
            'nik'             => ['nullable', 'string', 'max:16', 'regex:/^\d{16}$/'],
            'no_kk'           => ['nullable', 'string', 'max:16', 'regex:/^\d{16}$/'],
            'whatsapp'        => ['nullable', 'string', 'max:20'],
            'father_name'     => ['nullable', 'string', 'max:100'],
            'mother_name'     => ['nullable', 'string', 'max:100'],
            'parent_whatsapp' => ['nullable', 'string', 'max:20'],
            'is_abroad'       => ['nullable', 'boolean'],
            'country'         => ['nullable', 'string', 'max:100'],
            'province'        => ['nullable', 'string', 'max:100'],
            'regency'         => ['nullable', 'string', 'max:100'],
            'district'        => ['nullable', 'string', 'max:100'],
            'village'         => ['nullable', 'string', 'max:100'],
            'street'          => ['nullable', 'string', 'max:255'],
            'photo'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ], [
            'nik.regex'         => 'NIK harus 16 digit angka.',
            'no_kk.regex'       => 'Nomor KK harus 16 digit angka.',
            'birth_date.before' => 'Tanggal lahir tidak valid.',
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        if ($request->has('phone')) {
            $user->update(['phone' => $request->phone]);
        }

        // Bangun alamat lengkap
        $isAbroad = (bool) $request->is_abroad;
        $detailData['is_abroad'] = $isAbroad;
        if ($isAbroad) {
            $detailData['address'] = implode(', ', array_filter([
                $request->street, $request->country,
            ]));
        } else {
            $detailData['address'] = implode(', ', array_filter([
                $request->street, $request->village,
                $request->district, $request->regency,
                $request->province, 'Indonesia',
            ]));
        }

        if ($request->hasFile('photo')) {
            $old = $user->studentDetail?->photo_path;
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
            $detailData['photo_path'] = $request->file('photo')
                ->store('users/students/' . $user->school_id, 'public');
        }
        unset($detailData['photo']);

        StudentDetail::updateOrCreate(['user_id' => $user->id], $detailData);

        return redirect()->route('siswa.profile.show')
            ->with('success', 'Profil berhasil diperbarui.');
    }
}
