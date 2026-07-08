<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Position;
use App\Models\StudentDetail;
use App\Models\TeacherDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    // ── Index: daftar user per role ───────────────────────────────────────

    public function index(Request $request)
    {
        $school = auth()->user()->school;
        $tab    = $request->get('tab', 'siswa');

        $users = User::where('school_id', $school->id)
            ->where('role', $tab)
            ->with(['studentDetail', 'teacherDetail', 'positions',
                'classrooms' => fn($q) => $q->whereHas('academicYear', fn($q2) => $q2->where('is_active', true))
            ])
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $classrooms = Classroom::where('school_id', $school->id)
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->orderBy('name')->get();

        $positions = Position::where('school_id', $school->id)->orderBy('name')->get();

        return view('admin.users.index', compact('users', 'tab', 'classrooms', 'positions'));
    }

    // ── Form tambah ──────────────────────────────────────────────────────

    public function create(Request $request)
    {
        $school     = auth()->user()->school;
        $role       = $request->get('role', 'siswa');
        $classrooms = Classroom::where('school_id', $school->id)
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->orderBy('name')->get();
        $positions  = Position::where('school_id', $school->id)->orderBy('name')->get();

        return view('admin.users.create', compact('role', 'classrooms', 'positions'));
    }

    // ── Simpan user baru ─────────────────────────────────────────────────

    public function store(Request $request)
    {
        $school = auth()->user()->school;
        $role   = $request->input('role', 'siswa');

        // Validasi dasar
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:150'],
            'role'     => ['required', 'in:siswa,guru,wali_kelas,kesiswaan,admin,bendahara'],
            'username' => ['nullable', 'string', 'max:50', Rule::unique('users')->whereNull('deleted_at')],
            'nis'      => ['nullable', 'string', 'max:20', Rule::unique('users')->whereNull('deleted_at')],
            'nisn'     => ['nullable', 'digits:10', Rule::unique('users')->whereNull('deleted_at')],
            'nip'      => ['nullable', 'string', 'max:30'],
            'niy'      => ['nullable', 'string', 'max:30'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'email'    => ['nullable', 'email', 'max:100', Rule::unique('users')->whereNull('deleted_at')],
            'password' => ['required', 'string', 'min:6'],
            'is_active'=> ['boolean'],
        ]);

        $user = User::create([
            'school_id'  => $school->id,
            'name'       => $validated['name'],
            'role'       => $role,
            'username'   => $validated['username'] ?? null,
            'nis'        => $validated['nis'] ?? null,
            'nisn'       => $validated['nisn'] ?? null,
            'nip'        => $validated['nip'] ?? null,
            'niy'        => $validated['niy'] ?? null,
            'phone'      => $validated['phone'] ?? null,
            'email'      => $validated['email'] ?? null,
            'password'   => Hash::make($validated['password']),
            'is_active'  => $request->boolean('is_active', true),
        ]);

        // Simpan detail berdasarkan role
        if ($role === 'siswa') {
            $this->saveStudentDetail($request, $user);
            // Assign ke kelas
            if ($request->filled('classroom_id')) {
                $user->classrooms()->attach($request->classroom_id);
            }
        } else {
            $this->saveTeacherDetail($request, $user);
            // Assign jabatan
            if ($request->filled('position_ids')) {
                $user->positions()->sync($request->position_ids);
            }
        }

        return redirect()->route('admin.users.index', ['tab' => $role])
            ->with('success', 'User ' . $user->name . ' berhasil ditambahkan.');
    }

    // ── Form edit ─────────────────────────────────────────────────────────

    public function edit(User $user)
    {
        $school = auth()->user()->school;
        if ($user->school_id !== $school->id) abort(403);

        $user->load(['studentDetail', 'teacherDetail', 'positions', 'classrooms']);

        $classrooms = Classroom::where('school_id', $school->id)
            ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
            ->orderBy('name')->get();

        $positions = Position::where('school_id', $school->id)->orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'classrooms', 'positions'));
    }

    // ── Update user ───────────────────────────────────────────────────────

    public function update(Request $request, User $user)
    {
        $school = auth()->user()->school;
        if ($user->school_id !== $school->id) abort(403);

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:150'],
            'username' => ['nullable', 'string', 'max:50', Rule::unique('users')->ignore($user->id)->whereNull('deleted_at')],
            'nis'      => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user->id)->whereNull('deleted_at')],
            'nisn'     => ['nullable', 'digits:10', Rule::unique('users')->ignore($user->id)->whereNull('deleted_at')],
            'nip'      => ['nullable', 'string', 'max:30'],
            'niy'      => ['nullable', 'string', 'max:30'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'email'    => ['nullable', 'email', 'max:100', Rule::unique('users')->ignore($user->id)->whereNull('deleted_at')],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active'=> ['boolean'],
        ]);

        $updateData = [
            'name'      => $validated['name'],
            'username'  => $validated['username'] ?? null,
            'nis'       => $validated['nis'] ?? null,
            'nisn'      => $validated['nisn'] ?? null,
            'nip'       => $validated['nip'] ?? null,
            'niy'       => $validated['niy'] ?? null,
            'phone'     => $validated['phone'] ?? null,
            'email'     => $validated['email'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        if ($user->role === 'siswa') {
            $this->saveStudentDetail($request, $user);
            if ($request->filled('classroom_id')) {
                // Sync kelas aktif saja, tidak hapus kelas lama yang sudah tidak aktif
                $activeClassroomIds = Classroom::where('school_id', $school->id)
                    ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
                    ->pluck('id');
                $user->classrooms()->detach($activeClassroomIds);
                $user->classrooms()->attach($request->classroom_id);
            }
        } else {
            $this->saveTeacherDetail($request, $user);
            if ($request->has('position_ids')) {
                $user->positions()->sync($request->position_ids ?? []);
            }
        }

        return redirect()->route('admin.users.index', ['tab' => $user->role])
            ->with('success', 'Data ' . $user->name . ' berhasil diperbarui.');
    }

    // ── Nonaktifkan / aktifkan user ───────────────────────────────────────

    public function toggleActive(User $user)
    {
        $school = auth()->user()->school;
        if ($user->school_id !== $school->id) abort(403);

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success',
            $user->name . ' berhasil ' . ($user->is_active ? 'diaktifkan' : 'dinonaktifkan') . '.'
        );
    }

    // ── Hapus user (soft delete) ──────────────────────────────────────────

    public function destroy(User $user)
    {
        $school = auth()->user()->school;
        if ($user->school_id !== $school->id) abort(403);
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $user->delete();

        return back()->with('success', 'User ' . $user->name . ' berhasil dihapus.');
    }

    // ── Kelola jabatan (positions) ────────────────────────────────────────

    public function positions()
    {
        $school    = auth()->user()->school;
        $positions = Position::where('school_id', $school->id)
            ->withCount('teachers')
            ->orderBy('name')
            ->get();

        return view('admin.users.positions', compact('positions'));
    }

    public function storePosition(Request $request)
    {
        $school = auth()->user()->school;
        $request->validate([
            'name' => ['required', 'string', 'max:100',
                Rule::unique('positions')->where('school_id', $school->id)
            ],
        ]);

        Position::create(['school_id' => $school->id, 'name' => $request->name]);

        return back()->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function destroyPosition(Position $position)
    {
        $school = auth()->user()->school;
        if ($position->school_id !== $school->id) abort(403);

        $position->delete();
        return back()->with('success', 'Jabatan berhasil dihapus.');
    }

    // ── Private helpers ───────────────────────────────────────────────────

    private function saveStudentDetail(Request $request, User $user): void
    {
        $data = $request->validate([
            'birth_place'      => ['nullable', 'string', 'max:100'],
            'birth_date'       => ['nullable', 'date'],
            'address'          => ['nullable', 'string'],
            'gender'           => ['nullable', 'in:L,P'],
            'religion'         => ['nullable', 'in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu'],
            'nik'              => ['nullable', 'digits:16'],
            'no_kk'            => ['nullable', 'digits:16'],
            'whatsapp'         => ['nullable', 'string', 'max:20'],
            'father_name'      => ['nullable', 'string', 'max:100'],
            'mother_name'      => ['nullable', 'string', 'max:100'],
            'parent_whatsapp'  => ['nullable', 'string', 'max:20'],
            'photo'            => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            // Hapus foto lama
            if ($user->studentDetail?->photo_path) {
                Storage::disk('public')->delete($user->studentDetail->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store("users/students/{$user->school_id}", 'public');
        }
        unset($data['photo']);

        StudentDetail::updateOrCreate(['user_id' => $user->id], $data);
    }

    private function saveTeacherDetail(Request $request, User $user): void
    {
        $data = $request->validate([
            'birth_place'       => ['nullable', 'string', 'max:100'],
            'birth_date'        => ['nullable', 'date'],
            'address'           => ['nullable', 'string'],
            'gender'            => ['nullable', 'in:L,P'],
            'religion'          => ['nullable', 'in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu'],
            'employment_status' => ['nullable', 'in:ASN,PPPK,Kontrak,Honor,GTY'],
            'marital_status'    => ['nullable', 'in:Belum Kawin,Kawin,Cerai Hidup,Cerai Mati'],
            'children_count'    => ['nullable', 'integer', 'min:0'],
            'photo'             => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            if ($user->teacherDetail?->photo_path) {
                Storage::disk('public')->delete($user->teacherDetail->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store("users/teachers/{$user->school_id}", 'public');
        }
        unset($data['photo']);

        TeacherDetail::updateOrCreate(['user_id' => $user->id], $data);
    }
}
