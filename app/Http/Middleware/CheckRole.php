<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        // 1. Cek apakah user sudah login? Kalau belum, tendang ke login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Cek apakah role user sesuai?
        // Kalau halaman butuh 'admin', tapi user cuma 'guest', tolak!
        if (Auth::user()->role !== $role) {
            abort(403, 'AKSES DITOLAK: Anda bukan Admin!');
        }

        // Kalau aman, silakan lanjut
        return $next($request);
    }
}