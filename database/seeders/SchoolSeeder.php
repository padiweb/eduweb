<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        School::create([
            'name'                      => 'SMK Alhikmah Tanon',
            'slug'                      => 'smk-alhikmah-tanon',
            'address'                   => 'Jl. Raya Tanon, Sragen, Jawa Tengah',
            'phone'                     => '0271-XXXXXX',
            'email'                     => 'info@smkalhikmah-tanon.sch.id',
            'npsn'                      => '20313XXX',
            // Koordinat GPS sekolah — ganti dengan koordinat asli
            'latitude'                  => -7.4123456,
            'longitude'                 => 110.9876543,
            'attendance_radius_meters'  => 200,
            'school_start_time'         => '07:00:00',
            'late_threshold_time'       => '07:15:00',
            // Aktifkan semua fitur untuk development
            'feature_attendance'        => true,
            'feature_assignments'       => true,
            'feature_grades'            => true,
            'feature_violations'        => true,
            'feature_journal'           => true,
            'feature_prakerin'          => true,
            'feature_payment_info'      => true,
            'feature_cbt_integration'   => false,
            'package'                   => 'enterprise',
            'active_until'              => now()->addYear(),
            'is_active'                 => true,
        ]);
    }
}