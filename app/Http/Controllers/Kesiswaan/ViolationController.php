<?php

namespace App\Http\Controllers\Kesiswaan;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationCategory;
use App\Services\ViolationService;
use Illuminate\Http\Request;

class ViolationController extends Controller
{
    public function __construct(private ViolationService $service) {}

    // ── Daftar semua siswa + total poin ───────────────────────────────────

    public function index(Request $request)
    {
        $school = auth()->user()->school;

        $students = User::where('school_id', $school->id)
            ->where('role', 'siswa')
            ->with(['classrooms' => fn($q) =>
                $q->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ])
            ->withCount([
                'violations as total_points' => fn($q) =>
                    $q->where('is_archived', false)->select(\DB::raw('sum(points)')),
                'violations as violation_count' => fn($q) =>
                    $q->where('is_archived', false),
            ])
            ->orderByDesc('total_points')
            ->paginate(20);

        return view('kesiswaan.violations.index', compact('students'));
    }

    // ── Detail pelanggaran satu siswa ─────────────────────────────────────

    public function show(User $student)
    {
        $school = auth()->user()->school;

        if ($student->school_id !== $school->id) abort(403);

        $violations  = $this->service->getStudentViolations($student->id);
        $totalPoints = $this->service->getStudentPoints($student->id);
        $categories  = ViolationCategory::where('school_id', $school->id)->get();

        return view('kesiswaan.violations.show', compact(
            'student', 'violations', 'totalPoints', 'categories'
        ));
    }

    // ── Input pelanggaran manual ──────────────────────────────────────────

    public function store(Request $request)
    {
        $school = auth()->user()->school;

        $validated = $request->validate([
            'student_id'    => ['required', 'exists:users,id'],
            'category_id'   => ['required', 'exists:violation_categories,id'],
            'description'   => ['required', 'string', 'min:5', 'max:500'],
            'points'        => ['required', 'integer', 'min:1', 'max:100'],
            'incident_date' => ['required', 'date', 'before_or_equal:today'],
            'evidence_path' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        // Pastikan siswa dan kategori milik sekolah ini
        $student  = User::where('id', $validated['student_id'])
            ->where('school_id', $school->id)
            ->firstOrFail();

        $category = ViolationCategory::where('id', $validated['category_id'])
            ->where('school_id', $school->id)
            ->firstOrFail();

        // Upload bukti jika ada
        $evidencePath = null;
        if ($request->hasFile('evidence_path')) {
            $evidencePath = $request->file('evidence_path')
                ->store("violations/{$school->id}", 'public');
        }

        $this->service->createManualViolation(
            schoolId:     $school->id,
            studentId:    $student->id,
            categoryId:   $category->id,
            reportedBy:   auth()->id(),
            description:  $validated['description'],
            points:       (int) $validated['points'],
            incidentDate: $validated['incident_date'],
            evidencePath: $evidencePath,
        );

        return back()->with('success', 'Pelanggaran berhasil dicatat.');
    }

    // ── Arsipkan pelanggaran (tidak dihapus, hanya di-archive) ────────────

    public function archive(Violation $violation)
    {
        $school = auth()->user()->school;

        if ($violation->school_id !== $school->id) abort(403);

        // Hanya pelanggaran manual yang bisa diarsipkan oleh kesiswaan
        if ($violation->isAutomatic()) {
            return back()->with('error',
                'Pelanggaran otomatis tidak bisa diarsipkan. ' .
                'Koreksi status absensi melalui halaman Absensi Guru.'
            );
        }

        $violation->update(['is_archived' => true]);

        return back()->with('success', 'Pelanggaran berhasil diarsipkan.');
    }

    // ── Kelola kategori pelanggaran ───────────────────────────────────────

    public function categories()
    {
        $school      = auth()->user()->school;
        $categories  = ViolationCategory::where('school_id', $school->id)
            ->withCount('violations')
            ->orderBy('name')
            ->get();

        return view('kesiswaan.violations.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $school = auth()->user()->school;

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'severity'       => ['required', 'in:ringan,sedang,berat'],
            'default_points' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        ViolationCategory::create([
            'school_id'      => $school->id,
            'name'           => $validated['name'],
            'severity'       => $validated['severity'],
            'default_points' => $validated['default_points'],
        ]);

        return back()->with('success', 'Kategori pelanggaran berhasil ditambahkan.');
    }
}
