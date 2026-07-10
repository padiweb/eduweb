<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PaymentAccess
{
    // Role yang boleh akses modul keuangan (level apapun)
    const ALLOWED_ROLES = ['bendahara', 'kepala_sekolah', 'admin'];

    // Role yang boleh WRITE (input/approve/cancel)
    const WRITE_ROLES = ['bendahara'];

    // Role yang boleh approve/reject transaksi
    const CONFIRM_ROLES = ['bendahara'];

    public function handle(Request $request, Closure $next, string $permission = 'read'): mixed
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Siswa dan guru tidak boleh akses halaman keuangan admin sama sekali
        if (!in_array($user->role, self::ALLOWED_ROLES)) {
            abort(403, 'Anda tidak memiliki akses ke modul keuangan.');
        }

        // Cek permission spesifik
        if ($permission === 'write' && !in_array($user->role, self::WRITE_ROLES)) {
            abort(403, 'Hanya bendahara yang dapat melakukan perubahan data keuangan.');
        }

        if ($permission === 'confirm' && !in_array($user->role, self::CONFIRM_ROLES)) {
            abort(403, 'Hanya bendahara yang dapat mengkonfirmasi pembayaran.');
        }

        return $next($request);
    }
}
