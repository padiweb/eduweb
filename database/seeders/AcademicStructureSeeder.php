<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Major;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\School;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class AcademicStructureSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();

        // ── Tahun Ajaran ───────────────────────────────────────────────────
        $academicYear = AcademicYear::create([
            'school_id'  => $school->id,
            'name'       => '2024/2025',
            'semester'   => 2,
            'start_date' => '2025-01-06',
            'end_date'   => '2025-06-20',
            'is_active'  => true,
        ]);

        // ── Jurusan ────────────────────────────────────────────────────────
        $tkj = Major::create([
            'school_id' => $school->id,
            'name'      => 'Teknik Komputer dan Jaringan',
            'code'      => 'TKJ',
        ]);

        $rpl = Major::create([
            'school_id' => $school->id,
            'name'      => 'Rekayasa Perangkat Lunak',
            'code'      => 'RPL',
        ]);

        // ── Kelas ─────────────────────────────────────────────────────────
        $guruWali = User::where('role', 'wali_kelas')->first();
        $guru     = User::where('role', 'guru')->first();

        $kelas11TKJ1 = Classroom::create([
            'school_id'           => $school->id,
            'major_id'            => $tkj->id,
            'academic_year_id'    => $academicYear->id,
            'name'                => 'XI TKJ 1',
            'grade'               => 11,
            'homeroom_teacher_id' => $guruWali->id,
        ]);

        $kelas11RPL1 = Classroom::create([
            'school_id'           => $school->id,
            'major_id'            => $rpl->id,
            'academic_year_id'    => $academicYear->id,
            'name'                => 'XI RPL 1',
            'grade'               => 11,
            'homeroom_teacher_id' => $guru->id,
        ]);

        // ── Daftarkan siswa ke kelas XI TKJ 1 ─────────────────────────────
        $students = User::where('role', 'siswa')->get();

        foreach ($students as $index => $student) {
            ClassroomStudent::create([
                'classroom_id'   => $kelas11TKJ1->id,
                'student_id'     => $student->id,
                'student_number' => $index + 1,
            ]);
        }

        // ── Mata Pelajaran ─────────────────────────────────────────────────
        $subjects = [
            ['name' => 'Matematika',                    'code' => 'MTK',  'category' => 'A', 'major_id' => null],
            ['name' => 'Bahasa Indonesia',              'code' => 'BIND', 'category' => 'A', 'major_id' => null],
            ['name' => 'Bahasa Inggris',                'code' => 'BING', 'category' => 'B', 'major_id' => null],
            ['name' => 'Pendidikan Pancasila',          'code' => 'PPK',  'category' => 'A', 'major_id' => null],
            ['name' => 'Administrasi Sistem Jaringan',  'code' => 'ASJ',  'category' => 'C3', 'major_id' => $tkj->id],
            ['name' => 'Teknologi WAN',                 'code' => 'WAN',  'category' => 'C3', 'major_id' => $tkj->id],
            ['name' => 'Pemrograman Web',               'code' => 'PWB',  'category' => 'C3', 'major_id' => $rpl->id],
            ['name' => 'Basis Data',                    'code' => 'BDT',  'category' => 'C3', 'major_id' => $rpl->id],
        ];

        foreach ($subjects as $subject) {
            Subject::create([
                'school_id' => $school->id,
                'major_id'  => $subject['major_id'],
                'name'      => $subject['name'],
                'code'      => $subject['code'],
                'category'  => $subject['category'],
            ]);
        }
    }
}