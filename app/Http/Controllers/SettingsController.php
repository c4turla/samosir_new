<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Show settings page
     */
    public function index(Request $request)
    {
        return inertia('Settings/Index', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update application settings
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // Only admin can update app settings
        if ($user->role !== 'admin') {
            return back()->withErrors(['error' => 'Anda tidak memiliki izin untuk mengubah pengaturan.']);
        }

        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_description' => 'nullable|string|max:500',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
        ]);

        // Store settings in database or config
        // For now, we'll just return success
        return back()->with('success', 'Pengaturan berhasil diperbarui!');
    }
}