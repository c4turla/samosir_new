<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Vessel;

class AuthController extends Controller
{
    /**
     * Handle user register via Mobile App.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|string|email|max:150|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:umum,pengelola',
        ]);

        // Jika role pengelola, pastikan mengirim data kapal dan file pendukung
        if ($request->role === 'pengelola') {
            $request->validate([
                'vessel_id' => 'required|exists:vessels,id',
                'ktp_file' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
                'surat_kuasa_file' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
            ]);
        }

        DB::beginTransaction();

        try {
            $isActive = $request->role === 'umum' ? true : false;

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'is_active' => $isActive,
            ]);

            if ($request->role === 'pengelola') {
                $ktpPath = $request->file('ktp_file')->store('vessel_managers/ktp', 'public');
                $suratKuasaPath = $request->file('surat_kuasa_file')->store('vessel_managers/surat_kuasa', 'public');

                $user->vessels()->attach($request->vessel_id, [
                    'id_card' => $ktpPath,
                    'authorization_letter' => $suratKuasaPath,
                    'is_primary' => true,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => $request->role === 'pengelola' 
                    ? 'Registrasi berhasil. Akun Anda sedang menunggu persetujuan petugas.' 
                    : 'Registrasi berhasil. Silakan login.',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat registrasi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle user login via Mobile App.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email atau password salah.'
            ], 401);
        }

        // Cek apakah akun aktif
        if (!$user->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun Anda belum aktif atau sedang menunggu persetujuan admin/petugas.'
            ], 403);
        }

        // Cek apakah role diizinkan login via mobile
        if (!in_array($user->role, ['pengelola', 'umum'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role Anda tidak diizinkan untuk mengakses aplikasi mobile.'
            ], 403);
        }

        // Generate token Sanctum
        $token = $user->createToken('samosir-mobile')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'phone' => $user->phone,
                    'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
                ],
                'token' => $token,
            ]
        ], 200);
    }
    
    /**
     * Handle user logout via Mobile App.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil.'
        ], 200);
    }
    
    /**
     * Get authenticated user profile.
     */
    public function me(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
                'address' => $user->address,
                'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
            ]
        ]);
    }

    /**
     * Update authenticated user profile.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|required|string|max:150',
            'email' => ['sometimes', 'required', 'string', 'email', 'max:150', Rule::unique('users')->ignore($user->id)],
            'phone' => 'sometimes|nullable|string|max:30',
            'address' => 'sometimes|nullable|string',
            'photo' => 'sometimes|nullable|file|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Update text fields
        $fields = ['name', 'email', 'phone', 'address'];
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $user->$field = $request->$field;
            }
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Hapus foto lama jika bukan default
            if ($user->photo && $user->photo !== 'default.png') {
                Storage::disk('public')->delete($user->photo);
            }

            $path = $request->file('photo')->store('users/photos', 'public');
            $user->photo = $path;
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Profil berhasil diperbarui.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
                'address' => $user->address,
                'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
            ]
        ]);
    }

    /**
     * Change authenticated user password.
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Password lama tidak sesuai.'
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password berhasil diubah.'
        ]);
    }
}

