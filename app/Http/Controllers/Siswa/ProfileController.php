<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\StudentDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $user   = auth()->user();
        $detail = $user->studentDetail ?? new StudentDetail(['user_id' => $user->id]);
        return view('siswa.profile.edit', compact('user', 'detail'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        // Validasi data user
        $request->validate([
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        // Validasi detail biodata
        $detailData = $request->validate([
            'birth_place'      => ['nullable', 'string', 'max:100'],
            'birth_date'       => ['nullable', 'date', 'before:today'],
            'gender'           => ['nullable', 'in:L,P'],
            'religion'         => ['nullable', 'in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu'],
            'nik'              => ['nullable', 'string', 'max:16', 'regex:/^\d{16}$/'],
            'no_kk'            => ['nullable', 'string', 'max:16', 'regex:/^\d{16}$/'],
            'whatsapp'         => ['nullable', 'string', 'max:20'],
            'father_name'      => ['nullable', 'string', 'max:100'],
            'mother_name'      => ['nullable', 'string', 'max:100'],
            'parent_whatsapp'  => ['nullable', 'string', 'max:20'],
            // Alamat
            'is_abroad'        => ['nullable', 'boolean'],
            'country'          => ['nullable', 'string', 'max:100'],
            'province'         => ['nullable', 'string', 'max:100'],
            'regency'          => ['nullable', 'string', 'max:100'],
            'district'         => ['nullable', 'string', 'max:100'],
            'village'          => ['nullable', 'string', 'max:100'],
            'street'           => ['nullable', 'string', 'max:255'],
            // Foto
            'photo'            => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ], [
            'nik.regex'    => 'NIK harus 16 digit angka.',
            'no_kk.regex'  => 'Nomor KK harus 16 digit angka.',
            'birth_date.before' => 'Tanggal lahir tidak valid.',
        ]);

        // Update password jika diisi
        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        // Update phone
        if ($request->has('phone')) {
            $user->update(['phone' => $request->phone]);
        }

        // Bangun alamat lengkap untuk kolom address
        $isAbroad = (bool) $request->is_abroad;
        if ($isAbroad) {
            $detailData['address'] = implode(', ', array_filter([
                $request->street,
                $request->country,
            ]));
        } else {
            $detailData['address'] = implode(', ', array_filter([
                $request->street,
                $request->village,
                $request->district,
                $request->regency,
                $request->province,
                'Indonesia',
            ]));
        }
        $detailData['is_abroad'] = $isAbroad;

        // Handle foto
        if ($request->hasFile('photo')) {
            $old = $user->studentDetail?->photo_path;
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
            $detailData['photo_path'] = $request->file('photo')
                ->store('users/students/' . $user->school_id, 'public');
        }
        unset($detailData['photo']);

        // Simpan atau buat detail
        StudentDetail::updateOrCreate(
            ['user_id' => $user->id],
            $detailData
        );

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
