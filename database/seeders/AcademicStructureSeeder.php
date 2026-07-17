<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\Major;
use App\Models\School;
use App\Models\Subject;
use App\Models\SubjectGroup;
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
            'name'       => '2025/2026',
            'semester'   => 1,
            'start_date' => '2025-07-14',
            'end_date'   => '2025-12-20',
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

        // ── Kelompok Mata Pelajaran (pengganti category enum) ──────────────
        $groupA = SubjectGroup::create([
            'school_id' => $school->id,
            'name'      => 'Kelompok A (Umum)',
            'code'      => 'A',
        ]);

        $groupB = SubjectGroup::create([
            'school_id' => $school->id,
            'name'      => 'Kelompok B (Umum)',
            'code'      => 'B',
        ]);

        $groupC3 = SubjectGroup::create([
            'school_id' => $school->id,
            'name'      => 'Kelompok C3 (Kejuruan)',
            'code'      => 'C3',
        ]);

        // ── Kelas ──────────────────────────────────────────────────────────
        $guruWali = User::where('role', 'wali_kelas')->first();
        $guru     = User::where('role', 'guru')->first();

        $kelas11TKJ1 = Classroom::create([
            'school_id'           => $school->id,
            'major_id'            => $tkj->id,
            'academic_year_id'    => $academicYear->id,
            'name'                => 'XI TKJ 1',
            'slug'                => 'xi-tkj-1',
            'grade'               => 11,
            'homeroom_teacher_id' => $guruWali?->id,
        ]);

        $kelas11RPL1 = Classroom::create([
            'school_id'           => $school->id,
            'major_id'            => $rpl->id,
            'academic_year_id'    => $academicYear->id,
            'name'                => 'XI RPL 1',
            'slug'                => 'xi-rpl-1',
            'grade'               => 11,
            'homeroom_teacher_id' => $guru?->id,
        ]);

        // ── Daftarkan siswa ke kelas XI TKJ 1 ─────────────────────────────
        $students = User::where('school_id', $school->id)
            ->where('role', 'siswa')->get();

        foreach ($students as $index => $student) {
            ClassroomStudent::create([
                'classroom_id'   => $kelas11TKJ1->id,
                'student_id'     => $student->id,
                'student_number' => $index + 1,
            ]);
        }

        // ── Mata Pelajaran (pakai subject_group_id, bukan category) ────────
        $subjects = [
            ['name' => 'Matematika',                   'code' => 'MTK',  'group' => $groupA,  'major_id' => null],
            ['name' => 'Bahasa Indonesia',             'code' => 'BIND', 'group' => $groupA,  'major_id' => null],
            ['name' => 'Bahasa Inggris',               'code' => 'BING', 'group' => $groupB,  'major_id' => null],
            ['name' => 'Pendidikan Pancasila',         'code' => 'PPK',  'group' => $groupA,  'major_id' => null],
            ['name' => 'Administrasi Sistem Jaringan', 'code' => 'ASJ',  'group' => $groupC3, 'major_id' => $tkj->id],
            ['name' => 'Teknologi WAN',                'code' => 'WAN',  'group' => $groupC3, 'major_id' => $tkj->id],
            ['name' => 'Pemrograman Web',              'code' => 'PWB',  'group' => $groupC3, 'major_id' => $rpl->id],
            ['name' => 'Basis Data',                   'code' => 'BDT',  'group' => $groupC3, 'major_id' => $rpl->id],
        ];

        foreach ($subjects as $subject) {
            Subject::create([
                'school_id'        => $school->id,
                'major_id'         => $subject['major_id'],
                'subject_group_id' => $subject['group']->id,
                'name'             => $subject['name'],
                'code'             => $subject['code'],
            ]);
        }
    }
}
