<?php

namespace Database\Seeders;

use App\Models\PaymentType;
use App\Models\PaymentRate;
use App\Models\User;
use App\Models\School;
use App\Models\AcademicYear;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $school      = School::first();
        $academicYear = AcademicYear::where('school_id', $school->id)->first();

        if (!$school || !$academicYear) {
            $this->command->warn('School atau AcademicYear belum ada, skip PaymentSeeder.');
            return;
        }

        // ── Buat akun bendahara ────────────────────────────────────────────
        $bendahara = User::firstOrCreate(
            ['username' => 'bendahara', 'school_id' => $school->id],
            [
                'name'       => 'Bendahara Sekolah',
                'email'      => 'bendahara@' . str($school->name)->slug() . '.sch.id',
                'password'   => Hash::make('bendahara123'),
                'role'       => 'bendahara',
                'is_active'  => true,
                'school_id'  => $school->id,
            ]
        );

        // ── Buat akun kepala sekolah ───────────────────────────────────────
        User::firstOrCreate(
            ['username' => 'kepsek', 'school_id' => $school->id],
            [
                'name'       => 'Kepala Sekolah',
                'email'      => 'kepsek@' . str($school->name)->slug() . '.sch.id',
                'password'   => Hash::make('kepsek123'),
                'role'       => 'kepala_sekolah',
                'is_active'  => true,
                'school_id'  => $school->id,
            ]
        );

        // ── Jenis pembayaran ───────────────────────────────────────────────
        $types = [
            [
                'name'        => 'SPP',
                'code'        => 'SPP',
                'category'    => 'spp',
                'period_type' => 'monthly',
                'description' => 'Sumbangan Pembinaan Pendidikan bulanan',
            ],
            [
                'name'        => 'PAS Ganjil',
                'code'        => 'PAS1',
                'category'    => 'ujian',
                'period_type' => 'once',
                'description' => 'Penilaian Akhir Semester Ganjil',
            ],
            [
                'name'        => 'PAS Genap',
                'code'        => 'PAS2',
                'category'    => 'ujian',
                'period_type' => 'once',
                'description' => 'Penilaian Akhir Semester Genap / Kenaikan Kelas',
            ],
        ];

        $createdTypes = [];
        foreach ($types as $typeData) {
            $createdTypes[] = PaymentType::firstOrCreate(
                ['school_id' => $school->id, 'code' => $typeData['code']],
                array_merge($typeData, ['school_id' => $school->id])
            );
        }

        // ── Tarif SPP: berlaku untuk semua kelas ─────────────────────────
        $sppType = $createdTypes[0];
        PaymentRate::firstOrCreate(
            [
                'payment_type_id'  => $sppType->id,
                'school_id'        => $school->id,
                'academic_year_id' => $academicYear->id,
                'classroom_id'     => null,
                'major_id'         => null,
            ],
            [
                'amount'    => 200000, // Rp 200.000
                'is_active' => true,
            ]
        );

        $this->command->info("✓ PaymentSeeder: jenis pembayaran, tarif, bendahara & kepsek berhasil dibuat.");
        $this->command->info("  Login bendahara: username=bendahara, password=bendahara123");
        $this->command->info("  Login kepsek   : username=kepsek,    password=kepsek123");
    }
}
