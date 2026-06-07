<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureDocumentsCompleted
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->role === 'pengelola') {
            if (empty($user->id_card) || empty($user->authorization_letter)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda harus melengkapi dokumen KTP dan Surat Kuasa terlebih dahulu di profil Anda.'
                ], 403);
            }
        }

        return $next($request);
    }
}
