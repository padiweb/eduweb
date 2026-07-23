<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSchoolActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) return $next($request);

        // Cache school per user — 5 menit
        $school = cache()->remember("school_u{$user->id}", 300, fn() => $user->school);

        if (! $school || ! $school->is_active) {
            auth()->logout();
            cache()->forget("school_u{$user->id}");
            return redirect()->route('login')
                ->withErrors(['email' => 'Akun sekolah tidak aktif. Hubungi administrator.']);
        }

        if ($school->active_until && $school->active_until->isPast()) {
            auth()->logout();
            cache()->forget("school_u{$user->id}");
            return redirect()->route('login')
                ->withErrors(['email' => 'Masa aktif langganan sekolah telah berakhir.']);
        }

        // Timezone — set hanya jika belum diset
        if ($school->timezone && config('app.timezone') !== $school->timezone) {
            config(['app.timezone' => $school->timezone]);
            date_default_timezone_set($school->timezone);
        }

        return $next($request);
    }
}
