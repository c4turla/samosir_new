<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Show user profile page
     */
    public function show(Request $request)
    {
        return inertia('Profile/Show', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return back()->with('success', 'Profile berhasil diperbarui!');
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|confirmed|min:8',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Password saat ini tidak sesuai.',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password berhasil diperbarui!');
    }

    /**
     * Update user signature from canvas base64 data
     */
    public function updateSignature(Request $request)
    {
        $request->validate([
            'signature' => 'required|string',
        ]);

        $user = $request->user();
        
        // This feature is for syahbandar only
        if ($user->role !== 'syahbandar') {
            return back()->with('error', 'Fitur ini hanya tersedia untuk Syahbandar.');
        }

        $data = $request->signature;
        
        // Handle base64 image
        if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
            $data = substr($data, strpos($data, ',') + 1);
            $type = strtolower($type[1]); // png, jpg, etc

            if (!in_array($type, ['png', 'jpg', 'jpeg'])) {
                return back()->with('error', 'Format gambar tidak valid.');
            }

            $data = base64_decode($data);

            if ($data === false) {
                return back()->with('error', 'Gagal memproses gambar tanda tangan.');
            }
            
            $fileName = 'signatures/' . $user->id . '_' . time() . '.' . $type;
            
            // Delete old signature if exists
            if ($user->signature && \Storage::disk('public')->exists($user->signature)) {
                \Storage::disk('public')->delete($user->signature);
            }

            \Storage::disk('public')->put($fileName, $data);
            
            $user->update([
                'signature' => $fileName
            ]);

            return back()->with('success', 'Tanda tangan berhasil diperbarui!');
        }

        return back()->with('error', 'Data tanda tangan tidak valid.');
    }
}