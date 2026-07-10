<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\TeachingJournal;
use App\Models\TeacherRewardPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeachingJournalController extends Controller
{
    // ── Daftar jadwal hari ini + status jurnal ────────────────────────────

    public function index()
    {
        $teacher = auth()->user();
        $school  = $teacher->school;
        $today   = today();
        $dayNum  = $today->dayOfWeekIso; // 1=Senin ... 6=Sabtu

        // Jadwal guru hari ini
        $todaySchedules = Schedule::where('teacher_id', $teacher->id)
            ->where('school_id', $school->id)
            ->where('day_of_week', $dayNum)
            ->whereHas('classroom.academicYear', fn($q) => $q->where('is_active', true))
            ->with(['subject', 'classroom'])
            ->orderBy('start_time')
            ->get()
            ->map(function ($schedule) use ($today) {
                $journal = TeachingJournal::where('schedule_id', $schedule->id)
                    ->where('journal_date', $today)
                    ->first();
                $schedule->today_journal = $journal;
                return $schedule;
            });

        // Semua jadwal guru (untuk riwayat per mapel-kelas)
        $allSchedules = Schedule::where('teacher_id', $teacher->id)
            ->where('school_id', $school->id)
            ->whereHas('classroom.academicYear', fn($q) => $q->where('is_active', true))
            ->with(['subject', 'classroom'])
            ->orderBy('day_of_week')->orderBy('start_time')
            ->get()
            ->unique(fn($s) => $s->classroom_id . '-' . $s->subject_id);

        // Total jurnal bulan ini
        $journalThisMonth = TeachingJournal::where('teacher_id', $teacher->id)
            ->where('school_id', $school->id)
            ->whereMonth('journal_date', now()->month)
            ->whereYear('journal_date', now()->year)
            ->count();

        return view('guru.journal.index', compact(
            'todaySchedules', 'allSchedules', 'journalThisMonth', 'today'
        ));
    }

    // ── Form isi jurnal ───────────────────────────────────────────────────

    public function create(Request $request)
    {
        $teacher    = auth()->user();
        $school     = $teacher->school;
        $scheduleId = $request->get('schedule_id');
        $date       = $request->get('date', today()->format('Y-m-d'));

        $schedule = Schedule::where('id', $scheduleId)
            ->where('teacher_id', $teacher->id)
            ->where('school_id', $school->id)
            ->with(['subject', 'classroom.students'])
            ->firstOrFail();

        // Cek sudah ada jurnal untuk jadwal+tanggal ini
        $existing = TeachingJournal::where('schedule_id', $scheduleId)
            ->where('journal_date', $date)
            ->first();

        // Hitung pertemuan ke berapa
        $meetingNumber = TeachingJournal::where('schedule_id', $scheduleId)
            ->where('journal_date', '<=', $date)
            ->count() + ($existing ? 0 : 1);

        return view('guru.journal.create', compact(
            'schedule', 'date', 'existing', 'meetingNumber'
        ));
    }

    // ── Simpan jurnal ─────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $teacher = auth()->user();
        $school  = $teacher->school;

        $validated = $request->validate([
            'schedule_id'      => ['required', 'exists:schedules,id'],
            'journal_date'     => ['required', 'date'],
            'meeting_number'   => ['required', 'integer', 'min:1'],
            'topic'            => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string', 'max:2000'],
            'method'           => ['required', 'in:ceramah,diskusi,praktek,demonstrasi,presentasi,tanya_jawab,lainnya'],
            'students_present' => ['required', 'integer', 'min:0'],
            'students_absent'  => ['required', 'integer', 'min:0'],
            'notes'            => ['nullable', 'string', 'max:500'],
            'photo'            => ['nullable', 'image', 'max:3072'],
        ]);

        $schedule = Schedule::where('id', $validated['schedule_id'])
            ->where('teacher_id', $teacher->id)
            ->where('school_id', $school->id)
            ->firstOrFail();

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')
                ->store("journals/{$school->id}/{$teacher->id}", 'public');
        }

        $journal = TeachingJournal::updateOrCreate(
            [
                'schedule_id'  => $validated['schedule_id'],
                'journal_date' => $validated['journal_date'],
            ],
            [
                'school_id'        => $school->id,
                'teacher_id'       => $teacher->id,
                'classroom_id'     => $schedule->classroom_id,
                'subject_id'       => $schedule->subject_id,
                'meeting_number'   => $validated['meeting_number'],
                'topic'            => $validated['topic'],
                'description'      => $validated['description'] ?? null,
                'method'           => $validated['method'],
                'students_present' => $validated['students_present'],
                'students_absent'  => $validated['students_absent'],
                'notes'            => $validated['notes'] ?? null,
                'photo_path'       => $photoPath ?? null,
            ]
        );

        // Beri reward poin jika belum dapat hari ini
        if (! $journal->is_reward_given) {
            $alreadyGiven = TeacherRewardPoint::where('school_id', $school->id)
                ->where('teacher_id', $teacher->id)
                ->where('type', 'isi_jurnal')
                ->whereDate('point_date', $validated['journal_date'])
                ->where('reference_id', $journal->id)
                ->exists();

            if (! $alreadyGiven) {
                TeacherRewardPoint::create([
                    'school_id'      => $school->id,
                    'teacher_id'     => $teacher->id,
                    'type'           => 'isi_jurnal',
                    'points'         => 1,
                    'description'    => 'Isi jurnal: ' . $validated['topic'],
                    'point_date'     => $validated['journal_date'],
                    'reference_id'   => $journal->id,
                    'reference_type' => 'teaching_journal',
                ]);

                $journal->update(['is_reward_given' => true]);
            }
        }

        return redirect()->route('guru.journal.index')
            ->with('success', 'Jurnal mengajar berhasil disimpan. +1 poin reward!');
    }

    // ── Riwayat jurnal per mapel-kelas ────────────────────────────────────

    public function history(Request $request)
    {
        $teacher    = auth()->user();
        $school     = $teacher->school;
        $scheduleId = $request->get('schedule_id');

        $schedules = Schedule::where('teacher_id', $teacher->id)
            ->where('school_id', $school->id)
            ->whereHas('classroom.academicYear', fn($q) => $q->where('is_active', true))
            ->with(['subject', 'classroom'])
            ->orderBy('day_of_week')->orderBy('start_time')
            ->get()
            ->unique(fn($s) => $s->classroom_id . '-' . $s->subject_id);

        $journals = collect();
        $selectedSchedule = null;

        if ($scheduleId) {
            $selectedSchedule = Schedule::find($scheduleId);
            $journals = TeachingJournal::where('schedule_id', $scheduleId)
                ->where('teacher_id', $teacher->id)
                ->orderByDesc('journal_date')
                ->get();
        }

        return view('guru.journal.history', compact(
            'schedules', 'journals', 'scheduleId', 'selectedSchedule'
        ));
    }

    // ── Show detail jurnal ────────────────────────────────────────────────

    public function show(TeachingJournal $journal)
    {
        $teacher = auth()->user();
        if ($journal->teacher_id !== $teacher->id) abort(403);

        $journal->load(['schedule', 'classroom', 'subject']);
        return view('guru.journal.show', compact('journal'));
    }
}
