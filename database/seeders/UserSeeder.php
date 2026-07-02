<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();

        // ── ADMIN ──────────────────────────────────────────────────────────
        User::create([
            'school_id' => $school->id,
            'name'      => 'Administrator',
            'email'     => 'admin@simans.test',
            'username'  => 'admin',
            'password'  => Hash::make('password'),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        // ── GURU ───────────────────────────────────────────────────────────
        User::create([
            'school_id' => $school->id,
            'name'      => 'Budi Santoso, S.Pd',
            'email'     => 'budi@simans.test',
            'username'  => 'budi.santoso',
            'nip'       => '198501012010011001',
            'password'  => Hash::make('password'),
            'role'      => 'guru',
            'is_active' => true,
        ]);

        User::create([
            'school_id' => $school->id,
            'name'      => 'Siti Rahayu, S.Kom',
            'email'     => 'siti@simans.test',
            'username'  => 'siti.rahayu',
            'nip'       => '199001012015012002',
            'password'  => Hash::make('password'),
            'role'      => 'guru',
            'is_active' => true,
        ]);

        // ── WALI KELAS ─────────────────────────────────────────────────────
        User::create([
            'school_id' => $school->id,
            'name'      => 'Dewi Kusuma, S.Pd',
            'email'     => 'dewi@simans.test',
            'username'  => 'dewi.kusuma',
            'nip'       => '198801012012012003',
            'password'  => Hash::make('password'),
            'role'      => 'wali_kelas',
            'is_active' => true,
        ]);

        // ── KESISWAAN ──────────────────────────────────────────────────────
        User::create([
            'school_id' => $school->id,
            'name'      => 'Ahmad Fauzi, S.Pd',
            'email'     => 'fauzi@simans.test',
            'username'  => 'ahmad.fauzi',
            'nip'       => '197901012005011004',
            'password'  => Hash::make('password'),
            'role'      => 'kesiswaan',
            'is_active' => true,
        ]);

        // ── SISWA ──────────────────────────────────────────────────────────
        $students = [
            ['name' => 'Andika Wicaksono',   'nis' => '24100101'],
            ['name' => 'Dewi Safitri',        'nis' => '24100102'],
            ['name' => 'Ardi Nugroho',        'nis' => '24100103'],
            ['name' => 'Fajar Ramadan',       'nis' => '24100104'],
            ['name' => 'Nur Safitri',         'nis' => '24100105'],
            ['name' => 'Rizki Hidayat',       'nis' => '24100106'],
            ['name' => 'Sari Melati',         'nis' => '24100107'],
            ['name' => 'Doni Prasetyo',       'nis' => '24100108'],
            ['name' => 'Ayu Lestari',         'nis' => '24100109'],
            ['name' => 'Bagas Kurniawan',     'nis' => '24100110'],
        ];

        foreach ($students as $student) {
            User::create([
                'school_id' => $school->id,
                'name'      => $student['name'],
                'username'  => $student['nis'],
                'nis'       => $student['nis'],
                'password'  => Hash::make('password'),
                'role'      => 'siswa',
                'is_active' => true,
            ]);
        }
    }
}