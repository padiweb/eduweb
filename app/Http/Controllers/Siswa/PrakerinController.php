<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\PrakerinAbsence;
use App\Models\PrakerinAttendance;
use App\Models\PrakerinJournal;
use App\Models\PrakerinJournalPhoto;
use App\Models\PrakerinPlacement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PrakerinController extends Controller
{
    /** Placement aktif hari ini untuk siswa ini */
    private function activePlacement(int $userId): ?PrakerinPlacement
    {
        return PrakerinPlacement::with(['location', 'period'])
            ->where('student_id', $userId)
            ->where('is_active', true)
            ->whereHas('period', fn($q) => $q->where('is_active', true)
                ->where('start_date', '<=', today())
                ->where('end_date', '>=', today()))
            ->where(function ($q) {
                $today = today()->format('Y-m-d');
                $q->where(fn($q2) => $q2->whereNull('start_date')->orWhere('start_date', '<=', $today))
                  ->where(fn($q2) => $q2->whereNull('end_date')->orWhere('end_date', '>=', $today));
            })
            ->first();
    }

    // ── Dashboard ─────────────────────────────────────────────────────────

    public function index()
    {
        $user      = Auth::user();
        $placement = $this->activePlacement($user->id);
        $today     = today()->format('Y-m-d');
        $checkin = $checkout = $journal = $absence = null;
        $recentLogs = collect();

        if ($placement) {
            // Cek izin/sakit/libur hari ini
            $absence = PrakerinAbsence::where('placement_id', $placement->id)
                ->where('absence_date', $today)->where('status', 'approved')->first();

            // Checkin fisik = hanya status hadir/terlambat (bukan izin/sakit/libur)
            $checkin  = PrakerinAttendance::where('placement_id', $placement->id)
                ->where('attendance_date', $today)->where('type', 'check_in')
                ->whereIn('status', ['hadir', 'terlambat'])->first();
            $checkout = PrakerinAttendance::where('placement_id', $placement->id)
                ->where('attendance_date', $today)->where('type', 'check_out')
                ->whereIn('status', ['hadir', 'terlambat'])->first();
            $journal  = PrakerinJournal::where('placement_id', $placement->id)
                ->where('journal_date', $today)->first();
            $recentLogs = PrakerinAttendance::where('placement_id', $placement->id)
                ->where('attendance_date', '>=', today()->subDays(6)->format('Y-m-d'))
                ->whereIn('status', ['hadir', 'terlambat']) // hanya absen fisik
                ->orderByDesc('attendance_date')
                ->get()->groupBy(fn($a) => $a->attendance_date->format('Y-m-d'));
        }

        return view('siswa.prakerin.index', compact(
            'placement', 'checkin', 'checkout', 'journal', 'absence', 'recentLogs', 'today'
        ));
    }

    // ── Absensi ───────────────────────────────────────────────────────────

    public function absenPage(string $type)
    {
        abort_unless(in_array($type, ['check_in', 'check_out']), 404);
        $user      = Auth::user();
        $placement = $this->activePlacement($user->id);

        if (! $placement) {
            return redirect()->route('siswa.prakerin.index')
                ->with('error', 'Tidak ada penempatan prakerin aktif hari ini.');
        }

        $today = today()->format('Y-m-d');

        // Jika sudah lapor izin/sakit/libur, tidak bisa absen
        $absence = PrakerinAbsence::where('placement_id', $placement->id)
            ->where('absence_date', $today)->where('status', 'approved')->first();
        if ($absence) {
            return redirect()->route('siswa.prakerin.index')
                ->with('info', "Anda sudah lapor {$absence->type_label} hari ini. Absensi tidak diperlukan.");
        }

        $existing = PrakerinAttendance::where('placement_id', $placement->id)
            ->where('attendance_date', $today)->where('type', $type)
            ->whereIn('status', ['hadir', 'terlambat'])->first();

        if ($existing) {
            return redirect()->route('siswa.prakerin.index')
                ->with('info', 'Anda sudah ' . ($type === 'check_in' ? 'absen masuk' : 'absen pulang') . ' hari ini.');
        }

        // Lock: absen pulang hanya bisa jika sudah absen masuk
        if ($type === 'check_out') {
            $checkin = PrakerinAttendance::where('placement_id', $placement->id)
                ->where('attendance_date', $today)
                ->where('type', 'check_in')
                ->whereIn('status', ['hadir', 'terlambat'])
                ->first();

            if (! $checkin) {
                return redirect()->route('siswa.prakerin.index')
                    ->with('error', 'Anda belum absen masuk hari ini. Silakan absen masuk terlebih dahulu.');
            }
        }

        return view('siswa.prakerin.absen', compact('placement', 'type'));
    }

    public function absenStore(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'type'      => 'required|in:check_in,check_out',
            'selfie'    => 'required|string',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy'  => 'nullable|numeric',
        ]);

        $placement = $this->activePlacement($user->id);
        if (! $placement) {
            return response()->json(['success' => false, 'message' => 'Tidak ada penempatan aktif.'], 422);
        }

        $today = today()->format('Y-m-d');
        $type  = $request->type;

        if (PrakerinAttendance::where('placement_id', $placement->id)
            ->where('attendance_date', $today)->where('type', $type)->exists()) {
            return response()->json(['success' => false, 'message' => 'Sudah absen hari ini.'], 422);
        }

        $lat      = (float) $request->latitude;
        $lng      = (float) $request->longitude;
        $loc      = $placement->location;
        $distance = null;
        $within   = true;

        if ($loc->latitude && $loc->longitude) {
            $distance = $this->haversine($lat, $lng, (float)$loc->latitude, (float)$loc->longitude);
            $within   = $distance <= ($loc->radius_meters ?? 300);
        }

        // Cek terlambat
        $status      = 'hadir';
        $checkinTime = $loc->checkin_time;
        $lateAfter   = $loc->checkin_late_after;
        if ($type === 'check_in' && $checkinTime && $lateAfter) {
            $nowTime = now()->format('H:i');
            if ($nowTime > $lateAfter) $status = 'terlambat';
        }

        // Simpan selfie
        $selfieData  = preg_replace('/^data:image\/\w+;base64,/', '', $request->selfie);
        $selfiePath  = 'prakerin/selfie/' . date('Y/m') . '/' . $user->id . '_' . $type . '_' . time() . '.jpg';
        Storage::disk('public')->put($selfiePath, base64_decode($selfieData));

        PrakerinAttendance::create([
            'placement_id'          => $placement->id,
            'student_id'            => $user->id,
            'attendance_date'       => $today,
            'type'                  => $type,
            'status'                => $status,
            'selfie_path'           => $selfiePath,
            'selfie_taken_at'       => now(),
            'latitude'              => $lat,
            'longitude'             => $lng,
            'gps_accuracy'          => $request->accuracy,
            'distance_from_location'=> $distance,
            'is_within_geofence'    => $within,
            'ip_address'            => $request->ip(),
            'user_agent'            => $request->userAgent(),
        ]);

        $label = $type === 'check_in' ? 'Absen masuk' : 'Absen pulang';
        if ($status === 'terlambat') {
            return response()->json(['success' => true, 'warning' => true,
                'message' => "{$label} berhasil, tercatat terlambat."]);
        }
        if (! $within && $loc->latitude) {
            return response()->json(['success' => true, 'warning' => true,
                'message' => "{$label} berhasil, namun lokasi Anda " . round($distance) . "m dari {$loc->name} (di luar radius {$loc->radius_meters}m)."]);
        }
        return response()->json(['success' => true, 'message' => "{$label} berhasil dicatat."]);
    }

    // ── Jurnal ────────────────────────────────────────────────────────────

    public function jurnalPage(Request $request)
    {
        $user      = Auth::user();
        $placement = $this->activePlacement($user->id);
        if (! $placement) {
            return redirect()->route('siswa.prakerin.index')
                ->with('error', 'Tidak ada penempatan prakerin aktif.');
        }

        // Support isi jurnal hari lalu (maks 7 hari ke belakang)
        $date       = $request->get('date', today()->format('Y-m-d'));
        $dateCarbon = \Carbon\Carbon::parse($date);
        // Jurnal hanya bisa diisi untuk hari ini atau kemarin
        // (Lebih dari kemarin = sudah terlambat dan poin sudah diberikan)
        if ($dateCarbon->gt(today()) || $dateCarbon->lt(today()->subDays(1))) {
            $dateCarbon = today();
        }
        $date   = $dateCarbon->format('Y-m-d');
        $isLate = $dateCarbon->lt(today()); // kemarin = terlambat

        $journal = PrakerinJournal::with('photos')
            ->where('placement_id', $placement->id)
            ->where('journal_date', $date)->first();

        return view('siswa.prakerin.jurnal', compact('placement', 'journal', 'date', 'isLate'));
    }

    public function jurnalStore(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'content'      => 'required|string|min:50',
            'journal_date' => 'required|date',
            'photos'       => 'nullable|array|max:5',
            'photos.*'     => 'nullable|image|max:5120',
            'captions'     => 'nullable|array',
        ]);

        $placement = $this->activePlacement($user->id);
        if (! $placement) return back()->with('error', 'Tidak ada penempatan aktif.');

        $dateCarbon = \Carbon\Carbon::parse($request->journal_date);
        if ($dateCarbon->gt(today()) || $dateCarbon->lt(today()->subDays(1))) {
            return back()->with('error', 'Jurnal hanya bisa diisi untuk hari ini atau kemarin.');
        }
        $dateStr = $dateCarbon->format('Y-m-d');
        $isLate  = $dateCarbon->lt(today());

        $journal = PrakerinJournal::updateOrCreate(
            ['placement_id' => $placement->id, 'journal_date' => $dateStr],
            ['student_id' => $user->id, 'content' => $request->content,
             'status' => 'submitted', 'submitted_at' => now()]
        );

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $i => $photo) {
                if (! $photo) continue;
                $path = $photo->store('prakerin/journal/' . date('Y/m'), 'public');
                PrakerinJournalPhoto::create([
                    'journal_id' => $journal->id,
                    'photo_path' => $path,
                    'caption'    => $request->captions[$i] ?? null,
                    'sort_order' => $i,
                ]);
            }
        }

        return redirect()->route('siswa.prakerin.index')
            ->with('success', $isLate
                ? 'Jurnal tanggal ' . \Carbon\Carbon::parse($dateStr)->translatedFormat('d M Y') . ' berhasil disimpan. Poin pelanggaran tidak dapat dikurangi.'
                : 'Jurnal harian berhasil disimpan.');
    }

    public function deletePhoto(PrakerinJournalPhoto $photo)
    {
        $user = Auth::user();
        if ($photo->journal->student_id !== $user->id) abort(403);
        Storage::disk('public')->delete($photo->photo_path);
        $photo->delete();
        return back()->with('success', 'Foto dihapus.');
    }

    public function jurnalHistory()
    {
        $user = Auth::user();
        $placement = PrakerinPlacement::where('student_id', $user->id)
            ->where('is_active', true)
            ->whereHas('period', fn($q) => $q->where('is_active', true))
            ->first();

        if (! $placement) return redirect()->route('siswa.prakerin.index');

        $journals = PrakerinJournal::with('photos')
            ->where('placement_id', $placement->id)
            ->orderByDesc('journal_date')
            ->paginate(10);

        return view('siswa.prakerin.jurnal-history', compact('placement', 'journals'));
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R    = 6371000;
        $phi1 = deg2rad($lat1); $phi2 = deg2rad($lat2);
        $dphi = deg2rad($lat2 - $lat1); $dlam = deg2rad($lng2 - $lng1);
        $a    = sin($dphi/2)**2 + cos($phi1)*cos($phi2)*sin($dlam/2)**2;
        return $R * 2 * atan2(sqrt($a), sqrt(1-$a));
    }

    // ── Izin / Sakit / Libur ─────────────────────────────────────────────

    public function izinPage()
    {
        $user      = Auth::user();
        $placement = $this->activePlacement($user->id);
        if (! $placement) {
            return redirect()->route('siswa.prakerin.index')
                ->with('error', 'Tidak ada penempatan prakerin aktif.');
        }
        $today   = today()->format('Y-m-d');
        $absence = PrakerinAbsence::where('placement_id', $placement->id)
            ->where('absence_date', $today)->first();

        return view('siswa.prakerin.izin', compact('placement', 'absence', 'today'));
    }

    public function izinStore(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'type'       => 'required|in:izin,sakit,libur',
            'reason'     => 'required|string|min:10|max:500',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $placement = $this->activePlacement($user->id);
        if (! $placement) return back()->with('error', 'Tidak ada penempatan aktif.');

        $today = today()->format('Y-m-d');
        if (PrakerinAbsence::where('placement_id', $placement->id)->where('absence_date', $today)->exists()) {
            return back()->with('error', 'Sudah ada konfirmasi ketidakhadiran untuk hari ini.');
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')
                ->store('prakerin/absence/' . date('Y/m'), 'public');
        }

        // Langsung approved — tidak perlu persetujuan guru
        $absence = PrakerinAbsence::create([
            'placement_id'    => $placement->id,
            'student_id'      => $user->id,
            'absence_date'    => $today,
            'type'            => $request->type,
            'reason'          => $request->reason,
            'attachment_path' => $attachmentPath,
            'status'          => 'approved',
            'approved_at'     => now(),
        ]);

        // Langsung buat record attendance agar tidak kena poin pelanggaran
        PrakerinAttendance::updateOrCreate(
            [
                'placement_id'    => $placement->id,
                'attendance_date' => $today,
                'type'            => 'check_in',
            ],
            [
                'student_id' => $user->id,
                'status'     => $request->type, // izin / sakit / libur
                'ip_address' => $request->ip(),
            ]
        );

        $typeLabel = match($request->type) {
            'izin'  => 'Izin',
            'sakit' => 'Sakit',
            'libur' => 'Libur DU/DI',
            default => $request->type,
        };

        return redirect()->route('siswa.prakerin.index')
            ->with('success', "{$typeLabel} berhasil dicatat. Anda tidak akan tercatat alfa hari ini.");
    }
}

